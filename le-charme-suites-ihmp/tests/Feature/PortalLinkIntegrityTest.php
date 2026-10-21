<?php

namespace Tests\Feature;

use App\Models\CheckIn;
use App\Models\CheckOut;
use App\Models\Guest;
use App\Models\HousekeepingTask;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Signs in as each role, follows every same-site link and GET form the portal renders,
 * and fails if any of them leads to an error page. Set AUDIT_REPORT to a file path to also
 * write a JSON report that lists controls with no behavior.
 */
class PortalLinkIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_link_for_every_role_opens_a_working_page(): void
    {
        $this->seedSampleHotel();
        $report = [];

        foreach (['admin', 'receptionist', 'cashier', 'housekeeping'] as $role) {
            $user = User::where('role', $role)->firstOrFail();
            $report[$role] = $this->crawl($user);
        }

        if (getenv('AUDIT_REPORT')) {
            file_put_contents(getenv('AUDIT_REPORT'), json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        foreach ($report as $role => $result) {
            $this->assertSame([], $result['broken'], "Broken links found for the {$role} role.");
            $this->assertGreaterThan(5, $result['pages_crawled'], "Too few pages crawled for the {$role} role.");
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function crawl(User $user): array
    {
        $base = rtrim(config('app.url'), '/');
        $queue = [['/dashboard', 'login']];
        $seen = [];
        $broken = [];
        $deadControls = [];
        $pages = 0;

        while ($queue && $pages < 400) {
            [$path, $from] = array_shift($queue);
            $key = strtok($path, '#');

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $response = $this->actingAs($user)->get($key);
            $status = $response->getStatusCode();

            if ($status >= 400) {
                $broken[] = ['url' => $key, 'status' => $status, 'linked_from' => $from];

                continue;
            }

            if ($status >= 300) {
                $location = (string) $response->headers->get('Location');
                $queue[] = [str_replace($base, '', $location), $key];

                continue;
            }

            $contentType = (string) $response->headers->get('Content-Type');

            if (! str_contains($contentType, 'text/html')) {
                continue;
            }

            $pages++;
            $html = (string) $response->getContent();
            $dom = new \DOMDocument;
            @$dom->loadHTML($html);
            $xpath = new \DOMXPath($dom);

            foreach ($xpath->query('//a') as $anchor) {
                $href = trim($anchor->getAttribute('href'));
                $label = trim(preg_replace('/\s+/', ' ', $anchor->textContent));

                if ($href === '' || $href === '#' || str_starts_with($href, 'javascript')) {
                    if (! $anchor->hasAttribute('onclick') && ! $this->hasDataHook($anchor)) {
                        $deadControls[] = ['page' => $key, 'type' => 'link', 'label' => mb_substr($label, 0, 60), 'href' => $href];
                    }

                    continue;
                }

                if (str_starts_with($href, '#') || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) {
                    continue;
                }

                if (str_starts_with($href, $base)) {
                    $href = substr($href, strlen($base)) ?: '/';
                } elseif (preg_match('#^https?://#', $href)) {
                    continue;
                }

                if (str_contains($href, '/logout') || str_contains($href, '/export') || str_contains($href, 'transfer-report')) {
                    continue;
                }

                $queue[] = [$href, $key];
            }

            foreach ($xpath->query('//button') as $button) {
                $type = strtolower($button->getAttribute('type') ?: 'submit');
                $label = trim(preg_replace('/\s+/', ' ', $button->textContent));
                $insideForm = $xpath->query('ancestor::form', $button)->length > 0 || $button->hasAttribute('form');

                if ($type === 'submit' && $insideForm) {
                    continue;
                }

                if ($button->hasAttribute('onclick') || $button->hasAttribute('id') || $this->hasDataHook($button) || $button->hasAttribute('disabled')) {
                    continue;
                }

                $class = $button->getAttribute('class');
                $deadControls[] = ['page' => $key, 'type' => 'button', 'label' => mb_substr($label, 0, 60), 'class_hint' => mb_substr($class, 0, 40)];
            }

            foreach ($xpath->query('//form') as $form) {
                $action = trim($form->getAttribute('action'));
                $method = strtolower($form->getAttribute('method') ?: 'get');

                if ($action === '' || $action === '#') {
                    if ($form->getAttribute('id') === '' && ! $this->hasDataHook($form)) {
                        $deadControls[] = ['page' => $key, 'type' => 'form', 'label' => 'form without action', 'method' => $method];
                    }

                    continue;
                }

                if ($method === 'get' && str_starts_with($action, $base)) {
                    $queue[] = [substr($action, strlen($base)) ?: '/', $key];
                }
            }
        }

        return [
            'pages_crawled' => $pages,
            'urls_checked' => count($seen),
            'broken' => $broken,
            'dead_controls' => $this->uniqueControls($deadControls),
        ];
    }

    private function hasDataHook(\DOMElement $element): bool
    {
        foreach ($element->attributes as $attribute) {
            if (str_starts_with($attribute->name, 'data-') || str_starts_with($attribute->name, 'x-') || str_starts_with($attribute->name, '@')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<array<string, string>>  $controls
     * @return list<array<string, mixed>>
     */
    private function uniqueControls(array $controls): array
    {
        $grouped = [];

        foreach ($controls as $control) {
            $id = $control['type'].'|'.$control['label'];
            $grouped[$id] ??= [...$control, 'pages' => []];
            $grouped[$id]['pages'][$control['page']] = true;
        }

        return array_values(array_map(function (array $control): array {
            $control['pages'] = array_keys($control['pages']);
            unset($control['page']);

            return $control;
        }, $grouped));
    }

    private function seedSampleHotel(): void
    {
        foreach (['admin', 'receptionist', 'cashier', 'housekeeping'] as $role) {
            User::factory()->create(['role' => $role, 'name' => ucfirst($role).' Auditor']);
        }

        $rooms = collect(['101' => 'Standard Room', '102' => 'Deluxe King', '201' => 'Deluxe Twin', '202' => 'Charmé Suite', '301' => 'Family Room'])
            ->map(fn (string $type, string $number) => Room::factory()->create(['room_number' => $number, 'room_type' => $type, 'capacity' => 4]));

        $guest = Guest::factory()->create(['identification_type' => 'UMID', 'identification_number' => 'CRN-1']);

        Reservation::factory()->for($guest)->for($rooms['101'])->create();
        Reservation::factory()->pending()->for($rooms['102'])->create(['check_in_date' => today()->addDays(3), 'check_out_date' => today()->addDays(4)]);

        $inHouseRoom = $rooms['201'];
        $inHouseRoom->update(['status' => 'occupied']);
        $inHouse = Reservation::factory()->checkedIn()->for($inHouseRoom)->create(['check_in_date' => today()->subDay(), 'check_out_date' => today()->addDay()]);
        CheckIn::factory()->forReservation($inHouse)->create();

        $completed = Reservation::factory()->completed()->for($rooms['202'])->create(['check_in_date' => today()->subDays(4), 'check_out_date' => today()->subDays(2)]);
        $pastStay = CheckIn::factory()->forReservation($completed)->create(['status' => 'completed']);
        CheckOut::create([
            'check_in_id' => $pastStay->id,
            'reservation_id' => $completed->id,
            'guest_id' => $completed->guest_id,
            'room_id' => $completed->room_id,
            'checked_out_at' => now()->subDays(2),
            'amount_paid' => 39000,
            'payment_status' => 'paid',
        ]);

        $rooms['301']->update(['status' => 'cleaning']);
        HousekeepingTask::factory()->create(['room_id' => $rooms['301']->id, 'assigned_to' => User::where('role', 'housekeeping')->first()->id]);

        User::where('role', 'receptionist')->first()->sentDepartmentMessages()->create([
            'recipient_department' => 'all',
            'subject' => 'Audit message',
            'body' => 'Sample body',
            'priority' => 'normal',
        ]);
    }
}
