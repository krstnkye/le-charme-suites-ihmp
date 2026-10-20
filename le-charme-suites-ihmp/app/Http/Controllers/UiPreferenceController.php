<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UiPreferenceController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $preferences = $request->validate([
            'density' => ['required', Rule::in(['comfortable', 'compact'])],
            'font_scale' => ['required', Rule::in(['standard', 'large'])],
            'sidebar_default' => ['required', Rule::in(['expanded', 'collapsed'])],
        ]);

        $request->user()->update(['ui_preferences' => $preferences]);

        return response()->json([
            'message' => 'Interface preferences saved.',
            'preferences' => $preferences,
        ]);
    }
}
