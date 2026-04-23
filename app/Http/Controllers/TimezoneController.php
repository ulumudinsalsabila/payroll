<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TimezoneController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'timezone' => ['required', 'string', 'max:255'],
        ]);

        $timezone = $data['timezone'];

        if (!in_array($timezone, timezone_identifiers_list(), true)) {
            return response()->json([
                'message' => 'Timezone tidak valid.',
            ], 422);
        }

        $user = $request->user();
        if ($user && $user->timezone !== $timezone) {
            $user->timezone = $timezone;
            $user->save();
        }

        $request->session()->put('timezone', $timezone);

        return response()->json([
            'message' => 'Timezone berhasil disimpan.',
            'timezone' => $timezone,
        ]);
    }
}
