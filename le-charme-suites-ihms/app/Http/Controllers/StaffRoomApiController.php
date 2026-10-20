<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffRoomApiController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $limit = max(1, min($request->integer('limit', 50), 100));
        $rooms = Room::query()
            ->orderBy('room_number')
            ->limit($limit)
            ->get([
                'id',
                'room_number',
                'room_type',
                'status',
                'capacity',
                'rate',
            ]);

        return response()->json([
            'record_count' => $rooms->count(),
            'data' => $rooms,
        ]);
    }
}
