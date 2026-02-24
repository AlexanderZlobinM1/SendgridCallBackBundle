<?php

declare(strict_types=1);

return [
    'name'        => 'SendGrid Callback',
    'description' => 'SendGrid callback processing for Mautic. Company: Sales Snap. Author: Alexander Zlobin. Copyright (c) Sales Snap.',
    'author'      => 'Alexander Zlobin',
    'version'     => '1.2.0',
    'menu'        => [
        'main' => [
            'sendgrid_callback.menu.root' => [
                'id'        => 'sendgrid_callback_root',
                'iconClass' => 'ri-mail-send-fill',
                'priority'  => 55,
            ],
            'sendgrid_callback.menu.settings' => [
                'route'    => 'sendgrid_callback_settings',
                'parent'   => 'sendgrid_callback.menu.root',
                'priority' => 10,
                'access'   => 'admin',
            ],
        ],
    ],
    'routes'      => [
        'main' => [
            'sendgrid_callback_settings' => [
                'path'       => '/sendgrid-callback/settings',
                'controller' => '\\MauticPlugin\\SendgridCallbackBundle\\Controller\\SettingsController::indexAction',
            ],
        ],
    ],
    'parameters'  => [
        'sendgrid_callback_enabled'                  => true,
        'sendgrid_callback_handle_bounce'            => true,
        'sendgrid_callback_handle_blocked'           => true,
        'sendgrid_callback_handle_dropped'           => true,
        'sendgrid_callback_handle_spamreport'        => true,
        'sendgrid_callback_handle_unsubscribe'       => true,
        'sendgrid_callback_handle_group_unsubscribe' => true,
        'sendgrid_callback_dropped_policy'           => 'auto',
    ],
];
