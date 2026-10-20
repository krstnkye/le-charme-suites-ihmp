<?php

$actions = ['read', 'write', 'execute', 'override', 'export'];

$all = array_fill_keys($actions, true);
$none = array_fill_keys($actions, false);

return [
    'roles' => ['admin', 'receptionist', 'cashier', 'housekeeping'],

    'actions' => $actions,

    'capabilities' => [
        'reservations' => [
            'module' => 'Reservations',
            'icon' => 'calendar_month',
            'token' => 'reservations.manage',
            'description' => 'Create and modify room bookings, arrival dates, and guest assignments.',
            'risk' => 'High',
            'defaults' => [
                'admin' => $all,
                'receptionist' => ['read' => true, 'write' => true, 'execute' => true, 'override' => false, 'export' => true],
                'cashier' => ['read' => true, 'write' => false, 'execute' => false, 'override' => false, 'export' => false],
                'housekeeping' => ['read' => true, 'write' => false, 'execute' => false, 'override' => false, 'export' => false],
            ],
        ],
        'guests' => [
            'module' => 'Guest Records',
            'icon' => 'badge',
            'token' => 'guests.directory',
            'description' => 'View and maintain guest names, contact details, and stay records.',
            'risk' => 'Medium',
            'defaults' => [
                'admin' => $all,
                'receptionist' => ['read' => true, 'write' => true, 'execute' => true, 'override' => false, 'export' => false],
                'cashier' => ['read' => true, 'write' => false, 'execute' => false, 'override' => false, 'export' => false],
                'housekeeping' => $none,
            ],
        ],
        'rooms' => [
            'module' => 'Room Status and Inventory',
            'icon' => 'meeting_room',
            'token' => 'rooms.status_update',
            'description' => 'View room availability and update cleaning, occupied, and maintenance status.',
            'risk' => 'High',
            'defaults' => [
                'admin' => $all,
                'receptionist' => ['read' => true, 'write' => true, 'execute' => true, 'override' => false, 'export' => false],
                'cashier' => $none,
                'housekeeping' => ['read' => true, 'write' => true, 'execute' => true, 'override' => false, 'export' => false],
            ],
        ],
        'guest_bills' => [
            'module' => 'Guest Billing',
            'icon' => 'receipt_long',
            'token' => 'guest_bills.adjust',
            'description' => 'Review room charges, receive payments, and complete guest checkout.',
            'risk' => 'Critical',
            'defaults' => [
                'admin' => $all,
                'receptionist' => ['read' => true, 'write' => true, 'execute' => true, 'override' => false, 'export' => true],
                'cashier' => ['read' => true, 'write' => true, 'execute' => true, 'override' => true, 'export' => true],
                'housekeeping' => $none,
            ],
        ],
        'audit' => [
            'module' => 'Activity History and Reports',
            'icon' => 'shield',
            'token' => 'audit.export',
            'description' => 'Review system activity and export authorized operational reports.',
            'risk' => 'Critical',
            'defaults' => [
                'admin' => $all,
                'receptionist' => $none,
                'cashier' => ['read' => true, 'write' => false, 'execute' => false, 'override' => false, 'export' => true],
                'housekeeping' => $none,
            ],
        ],
    ],
];
