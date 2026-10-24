<?php

namespace App\Http\Controllers;

use App\Models\DepartmentMessage;
use App\Models\Guest;
use App\Models\HotelTransaction;
use App\Models\IntegrationEvent;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\DataExchangeService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AdminIntegrationCenterController extends Controller
{
    public function index(Request $request, DataExchangeService $exchange): View
    {
        return view('admin.integration.index', [
            'datasets' => $exchange->supportedDatasets(),
            'events' => IntegrationEvent::with('actor')->latest('occurred_at')->limit(30)->get(),
            'databaseStatus' => $this->databaseStatus(),
            'recordCounts' => [
                'Guests' => Guest::count(),
                'Property Rooms' => (int) config('hotel.published_room_count', Room::count()),
                'Rooms Encoded' => Room::count(),
                'Reservations' => Reservation::count(),
                'Transactions' => HotelTransaction::count(),
                'Messages' => DepartmentMessage::count(),
            ],
            'apiEnabled' => filled(config('integrations.api_key')),
            'websiteConnector' => config('integrations.official_website'),
            'onlineBookingCount' => Reservation::whereIn('source', ['public_website', 'hotel_website_api', 'official_website_simulation'])->count(),
            'simulatedWebsiteBookingCount' => Reservation::where('source', 'official_website_simulation')->count(),
            'networkMode' => in_array($request->getHost(), ['127.0.0.1', 'localhost'], true) ? 'Local workstation' : 'Shared hotel intranet',
            'memoryUsage' => memory_get_usage(true),
            'memoryLimit' => ini_get('memory_limit'),
        ]);
    }

    /**
     * Health of the active database: the local SQLite file, or a hosted PostgreSQL database such as Supabase.
     *
     * @return array{driver: string, path: string, size: int, writable: bool, is_cloud: bool}
     */
    private function databaseStatus(): array
    {
        $driver = (string) config('database.default');

        if ($driver === 'pgsql') {
            $connection = config('database.connections.pgsql');
            $host = filled($connection['url'] ?? null)
                ? (string) parse_url((string) $connection['url'], PHP_URL_HOST)
                : (string) ($connection['host'] ?? '');

            try {
                $size = (int) DB::scalar('select pg_database_size(current_database())');
                $isConnected = true;
            } catch (Throwable) {
                $size = 0;
                $isConnected = false;
            }

            return ['driver' => $driver, 'path' => $host, 'size' => $size, 'writable' => $isConnected, 'is_cloud' => true];
        }

        $databasePath = (string) config('database.connections.sqlite.database');
        if ($databasePath !== '' && $databasePath !== ':memory:' && ! str_starts_with($databasePath, DIRECTORY_SEPARATOR) && ! preg_match('/^[A-Za-z]:[\\\\\/]/', $databasePath)) {
            $databasePath = base_path($databasePath);
        }

        $databaseIsFile = $databasePath !== ':memory:' && is_file($databasePath);

        return [
            'driver' => $driver,
            'path' => $databasePath,
            'size' => $databaseIsFile ? filesize($databasePath) : 0,
            'writable' => $databasePath === ':memory:' || ($databaseIsFile && is_writable($databasePath)),
            'is_cloud' => false,
        ];
    }

    public function export(Request $request, DataExchangeService $exchange, string $dataset, string $format): Response
    {
        abort_unless(in_array($format, ['csv', 'json'], true), 404);

        try {
            $records = $exchange->export($dataset, config('integrations.max_export_records'));
        } catch (InvalidArgumentException) {
            abort(404);
        }

        $contents = $format === 'json' ? $this->toJson($dataset, $records) : $this->toCsv($records);
        $checksum = hash('sha256', $contents);

        IntegrationEvent::create([
            'actor_id' => $request->user()->id,
            'direction' => 'outbound',
            'channel' => 'file_download',
            'dataset' => $dataset,
            'format' => $format,
            'record_count' => $records->count(),
            'status' => 'completed',
            'checksum' => $checksum,
            'endpoint' => $request->path(),
            'metadata' => ['ip_address' => $request->ip(), 'filename' => "ihmp-{$dataset}.{$format}"],
            'occurred_at' => now(),
        ]);

        $contentType = $format === 'json' ? 'application/json' : 'text/csv; charset=UTF-8';

        return response($contents, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="ihmp-'.$dataset.'-'.now()->format('Ymd-His').'.'.$format.'"',
            'X-IHMP-Checksum-SHA256' => $checksum,
        ]);
    }

    public function transferReport(): Response
    {
        $records = IntegrationEvent::with('actor')->latest('occurred_at')->limit(500)->get()->map(fn (IntegrationEvent $event) => [
            'occurred_at' => $event->occurred_at?->toIso8601String(),
            'actor' => $event->actor?->email ?? 'API client',
            'direction' => $event->direction,
            'channel' => $event->channel,
            'dataset' => $event->dataset,
            'format' => $event->format,
            'record_count' => $event->record_count,
            'status' => $event->status,
            'checksum_sha256' => $event->checksum,
            'endpoint' => $event->endpoint,
        ]);

        return response($this->toCsv($records), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="ihmp-file-transfer-report-'.now()->format('Ymd-His').'.csv"',
        ]);
    }

    private function toJson(string $dataset, Collection $records): string
    {
        return (string) json_encode([
            'dataset' => $dataset,
            'record_count' => $records->count(),
            'generated_at' => now(config('app.timezone'))->toIso8601String(),
            'data' => $records->values(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function toCsv(Collection $records): string
    {
        $stream = fopen('php://temp', 'r+');
        $first = $records->first();

        if (is_array($first)) {
            fputcsv($stream, array_keys($first));
            foreach ($records as $row) {
                fputcsv($stream, array_map([$this, 'csvValue'], $row));
            }
        }

        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        return "\xEF\xBB\xBF".($contents ?: '');
    }

    private function csvValue(mixed $value): string
    {
        $text = is_array($value) ? json_encode($value) : (string) ($value ?? '');

        return preg_match('/^[=+\-@\t\r]/', $text) ? "'{$text}" : $text;
    }
}
