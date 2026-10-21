<?php

namespace App\Http\Controllers;

use App\Models\LostFoundItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LostFoundItemController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'item_name' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::in(array_keys(LostFoundItem::CATEGORIES))],
            'description' => ['nullable', 'string', 'max:1000'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'found_location' => ['nullable', 'required_without:room_id', 'string', 'max:120'],
            'storage_location' => ['required', 'string', 'max:120'],
        ], [
            'found_location.required_without' => 'Choose the room or describe where the item was found.',
        ]);

        $item = LostFoundItem::create($data + [
            'found_at' => now(),
            'found_by' => $request->user()->id,
            'status' => 'in_custody',
        ]);

        return back()->with('status', "{$item->item_name} logged as {$item->reference()}.");
    }

    public function update(Request $request, LostFoundItem $lostFoundItem): RedirectResponse
    {
        abort_if($lostFoundItem->status !== 'in_custody', 422, 'This item has already been released.');

        $data = $request->validate([
            'status' => ['required', Rule::in(['claimed', 'disposed'])],
            'claimed_by_name' => ['nullable', 'required_if:status,claimed', 'string', 'max:120'],
            'claimant_id_details' => ['nullable', 'required_if:status,claimed', 'string', 'max:120'],
            'release_notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'claimed_by_name.required_if' => 'Enter the name of the person claiming the item.',
            'claimant_id_details.required_if' => 'Record the ID presented by the claimant.',
        ]);

        $lostFoundItem->update($data + [
            'released_at' => now(),
            'released_by' => $request->user()->id,
        ]);

        return back()->with('status', $data['status'] === 'claimed'
            ? "{$lostFoundItem->reference()} released to {$data['claimed_by_name']}."
            : "{$lostFoundItem->reference()} recorded as donated / disposed.");
    }
}
