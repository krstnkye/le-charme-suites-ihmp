<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AdminSystemSettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'property' => [
                'name' => 'Le Charme Suites - Subic',
                'registration_number' => 'SBFZ-HOTEL-2018-0941-A',
                'currency' => 'PHP',
                'timezone' => config('app.timezone', 'Asia/Manila'),
                'tin' => '009-418-204-00000',
            ],
            'operations' => [
                'check_in' => '14:00',
                'check_out' => '12:00',
                'grace_period' => 60,
                'late_checkout_fee' => 'PHP 500/hr',
                'auto_release' => '18:00',
            ],
            'taxes' => [
                'eco_tourism_fee' => 100,
                'service_charge' => 10,
                'vat_exemption_enabled' => true,
            ],
        ]);
    }
}
