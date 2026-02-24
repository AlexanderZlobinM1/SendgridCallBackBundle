<?php

declare(strict_types=1);

namespace MauticPlugin\SendgridCallbackBundle;

use Mautic\IntegrationsBundle\Bundle\AbstractPluginBundle;

class SendgridCallbackBundle extends AbstractPluginBundle
{
    public const VERSION = '1.5.2';

    public const SUPPORTED_MAILER_SCHEMES = [
        'sendgrid',
        'sendgrid+smtp',
        'sendgrid+api',
    ];
}
