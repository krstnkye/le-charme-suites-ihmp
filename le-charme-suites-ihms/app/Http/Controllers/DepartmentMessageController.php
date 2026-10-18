<?php

namespace App\Http\Controllers;

use App\Models\DepartmentMessage;
use App\Models\IntegrationEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentMessageController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($request->user()->role === 'owner') {
            return redirect()->route('owner.page', ['page' => 'messages']);
        }

        return view('messages.index', $this->inboxData($request));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'recipient_department' => ['required', Rule::in(array_keys($this->departments()))],
            'subject' => ['required', 'string', 'max:140'],
            'body' => ['required', 'string', 'max:5000'],
            'priority' => ['required', Rule::in(['normal', 'important', 'urgent'])],
        ]);

        $message = $request->user()->sentDepartmentMessages()->create($data);

        IntegrationEvent::create([
            'actor_id' => $request->user()->id,
            'direction' => 'internal',
            'channel' => 'department_message',
            'dataset' => 'department_messages',
            'format' => 'database',
            'record_count' => 1,
            'status' => 'completed',
            'checksum' => hash('sha256', implode('|', [$message->id, $message->recipient_department, $message->subject, $message->created_at])),
            'endpoint' => $request->path(),
            'metadata' => ['recipient_department' => $message->recipient_department],
            'occurred_at' => now(),
        ]);

        return back()->with('status', 'Department message sent and stored in the shared history.');
    }

    public function markRead(Request $request, DepartmentMessage $message): RedirectResponse
    {
        abort_unless($this->canRead($request, $message), 403);

        $message->readers()->syncWithoutDetaching([
            $request->user()->id => ['read_at' => now()],
        ]);

        return back()->with('status', 'Message marked as read.');
    }

    public function inboxData(Request $request): array
    {
        $user = $request->user();
        $query = DepartmentMessage::query()
            ->with(['sender', 'readers' => fn ($query) => $query->where('users.id', $user->id)]);

        if ($user->role !== 'admin') {
            $query->where(function (Builder $query) use ($user): void {
                $query->where('sender_id', $user->id)
                    ->orWhereIn('recipient_department', ['all', $user->role]);
            });
        }

        $messages = $query->latest()->limit(100)->get();

        return [
            'user' => $user,
            'messages' => $messages,
            'departments' => $this->departments(),
            'unreadCount' => $messages->filter(fn (DepartmentMessage $message): bool =>
                $message->sender_id !== $user->id && $message->readers->isEmpty()
            )->count(),
        ];
    }

    private function canRead(Request $request, DepartmentMessage $message): bool
    {
        $user = $request->user();

        return $user->role === 'admin'
            || $message->sender_id === $user->id
            || in_array($message->recipient_department, ['all', $user->role], true);
    }

    private function departments(): array
    {
        return [
            'all' => 'All Departments',
            'admin' => 'Administration',
            'receptionist' => 'Front Desk',
            'cashier' => 'Billing',
            'housekeeping' => 'Housekeeping',
            'owner' => 'Owner / Management',
        ];
    }
}
