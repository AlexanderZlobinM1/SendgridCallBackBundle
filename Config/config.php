<?php

declare(strict_types=1);

return [
    'name'        => 'SendGrid Callback',
    'description' => 'SendGrid callback processing for Mautic. Company: Sales Snap. Author: Alexander Zlobin. Copyright (c) Sales Snap.',
    'author'      => 'Alexander Zlobin',
    'version'     => '1.3.0',
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
