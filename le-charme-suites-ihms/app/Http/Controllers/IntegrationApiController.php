<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\HotelTransaction;
use App\Models\IntegrationEvent;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\DataExchangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class IntegrationApiController extends Controller
{
    public function status(): JsonResponse
    {
        return response()->json([
            'system' => config('app.name'),
            'version' => '1.0',
            'status' => 'operational',
            'mode' => 'local-first intranet',
            'internet_required' => false,
            'database' => config('database.default'),
            'server_time' => now(config('app.timezone'))->toIso8601String(),
            'record_counts' => [
                'guests' => Guest::count(),
                'property_rooms' => (int) config('hotel.published_room_count', Room::count()),
                'rooms_encoded' => Room::count(),
                'reservations' => Reservation::count(),
                'transactions' => HotelTransaction::count(),
            ],
        ]);
    }

    public function dataset(Request $request, DataExchangeService $exchange, string $dataset): JsonResponse
    {
        try {
            $records = $exchange->export($dataset, $request->integer('limit', 100));
        } catch (InvalidArgumentException) {
            return response()->json([
                'message' => 'Unsupported dataset.',
                'supported_datasets' => $exchange->supportedDatasets(),
            ], 404);
        }

        $encoded = json_encode($records->values(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        IntegrationEvent::create([
            'direction' => 'outbound',
            'channel' => 'api',
            'dataset' => $dataset,
            'format' => 'json',
            'record_count' => $records->count(),
            'status' => 'completed',
            'checksum' => hash('sha256', $encoded ?: '[]'),
            'endpoint' => $request->path(),
            'metadata' => ['ip_address' => $request->ip()],
            'occurred_at' => now(),
        ]);

        return response()->json([
            'dataset' => $dataset,
            'record_count' => $records->count(),
            'generated_at' => now(config('app.timezone'))->toIso8601String(),
            'data' => $records->values(),
        ]);
    }
}
