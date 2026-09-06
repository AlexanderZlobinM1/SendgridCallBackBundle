<?php

declare(strict_types=1);

use Mautic\CoreBundle\Helper\AppVersion;
use MauticPlugin\SendgridCallbackBundle\Integration\SendgridCallbackIntegration;

$mauticVersion = (int) (new AppVersion())->getVersion();

switch (true) {
    case $mauticVersion >= 6:
        $defaultIntegrationArguments = [
            'event_dispatcher',
            'mautic.helper.cache_storage',
            'doctrine.orm.entity_manager',
            'request_stack',
            'router',
            'translator',
            'monolog.logger.mautic',
            'sendgridcallbackbundle.helper.encryption',
            'mautic.lead.model.lead',
            'mautic.lead.model.company',
            'mautic.helper.paths',
            'mautic.core.model.notification',
            'mautic.lead.model.field',
            'mautic.plugin.model.integration_entity',
            'mautic.lead.model.dnc',
            ...($mauticVersion >= 6 ? ['mautic.lead.field.fields_with_unique_identifier'] : []),
        ];
        break;
    case $mauticVersion >= 5:
        $defaultIntegrationArguments = [
            'event_dispatcher',
            'mautic.helper.cache_storage',
            'doctrine.orm.entity_manager',
            'session',
            'request_stack',
            'router',
            'translator',
            'monolog.logger.mautic',
            'sendgridcallbackbundle.helper.encryption',
            'mautic.lead.model.lead',
            'mautic.lead.model.company',
            'mautic.helper.paths',
            'mautic.core.model.notification',
            'mautic.lead.model.field',
            'mautic.plugin.model.integration_entity',
            'mautic.lead.model.dnc',
            ...($mauticVersion >= 6 ? ['mautic.lead.field.fields_with_unique_identifier'] : []),
        ];
        break;
    default:
        $defaultIntegrationArguments = [];
}

return [
    'name'        => 'SendGrid Callback',
    'description' => 'SendGrid callback processing for Mautic. Company: Sales Snap. Author: Alexander Zlobin. Copyright (c) Sales Snap.',
    'author'      => 'Alexander Zlobin',
    'version'     => '1.5.9',
    'services'    => [
        'integrations' => [
            'mautic.integration.sendgridcallback' => [
                'class'     => SendgridCallbackIntegration::class,
                'arguments' => $defaultIntegrationArguments,
            ],
        ],
    ],
    'parameters'  => [],
];
