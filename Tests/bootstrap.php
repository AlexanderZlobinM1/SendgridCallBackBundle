<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

// Test doubles only. The separate runtime compatibility test uses unmodified core classes.
DG\BypassFinals::enable();

foreach ([Mautic\CoreBundle\Helper\CoreParametersHelper::class, Mautic\EmailBundle\Model\TransportCallback::class, Mautic\PluginBundle\Entity\Integration::class, Mautic\PluginBundle\Helper\IntegrationHelper::class, Mautic\PluginBundle\Integration\AbstractIntegration::class] as $class) {
    class_exists($class);
}
// BypassFinals wraps files and cannot fsync(). Restore real file I/O after loading mock targets.
stream_wrapper_restore('file');
