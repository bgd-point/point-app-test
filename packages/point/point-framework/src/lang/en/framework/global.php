<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Global Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the layout views. Feel free to tweak each of these messages here.
    |
    */

    'temporary_access'  => 'This access only available for this day only',
    'formulir'  => [
        'close'     => [
            'success'       => 'formulir ":form_number" successfully closed',
            'failed'        => 'formulir ":form_number" failed to close',
            'timeline'      => 'close formulir ":form_number"',
        ],
        'reopen'    => [
            'success'       => 'formulir ":form_number" successfully open',
            'failed'        => 'formulir ":form_number" failed to open',
            'timeline'      => 'open formulir ":form_number"',
        ],
        'create'    => [
            'success'       => 'form created successfully',
            'failed'        => 'failed to create from',
            'timeline'      => 'create new form',
        ],
        'update'    => [
            'success'       => 'form updated successfully',
            'failed'        => 'failed to update from',
            'timeline'      => 'update new form',
        ],
        'error'    => [
            'date'       =>  [
                'lower'       => 'your form date must be higher than your references',
                'locked'      => 'this date is already locked for new input',
            ],
            'restricted'       => 'not allowed to save this form',
        ],
    ],

    'button'     => [
        'list' => 'list',
        'create' => 'create',
        'edit' => 'edit',
        'delete' => 'delete',
        'access' => 'access',
        'show' => 'show',
        'submit' => 'submit',
        'cancel' => 'cancel',
        'search' => 'search',
        'approve' => 'approve',
        'reject' => 'reject',
        'request_approval' => 'request approval',
    ],

    'history'     => [
        'date' => 'date',
        'user' => 'user',
        'key' => 'key',
        'old_value' => 'old_value',
        'new_value' => 'new_value',
    ],

    'sidebar_menu'     => [
        'dashboard' => 'dashboard',
        'refresh' => 'refresh',
        'master' => 'master',
        'inventory' => 'inventory',
        'expedition' => 'expedition',
        'purchasing' => 'purchasing',
        'sales' => 'sales',
        'production' => 'production',
        'finance' => 'finance',
        'accounting' => 'accounting',
        'facility' => 'facility',
    ],
];
