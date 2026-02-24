<?php

declare(strict_types=1);

namespace MauticPlugin\SendgridCallbackBundle\EventListener;

use Mautic\ConfigBundle\ConfigEvents;
use Mautic\ConfigBundle\Event\ConfigBuilderEvent;
use Mautic\ConfigBundle\Event\ConfigEvent;
use MauticPlugin\SendgridCallbackBundle\Form\Type\ConfigType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigEvents::CONFIG_ON_GENERATE => 'onConfigGenerate',
            ConfigEvents::CONFIG_PRE_SAVE    => 'onConfigSave',
        ];
    }

    public function onConfigGenerate(ConfigBuilderEvent $event): void
    {
        $event->addForm([
            'bundle'     => 'SendgridCallbackBundle',
            'formAlias'  => 'sendgridcallback_config',
            'formType'   => ConfigType::class,
            'formTheme'  => '@SendgridCallback/FormTheme/Config/_config_sendgridcallback_config_widget.html.twig',
            'parameters' => $event->getParametersFromConfig('SendgridCallbackBundle'),
        ]);
    }

    public function onConfigSave(ConfigEvent $event): void
    {
        $values = $event->getConfig();

        if (!isset($values['sendgridcallback_config']) || !is_array($values['sendgridcallback_config'])) {
            return;
        }

        $config = $values['sendgridcallback_config'];

        $booleanFields = [
            'sendgrid_callback_handle_bounce',
            'sendgrid_callback_handle_blocked',
            'sendgrid_callback_handle_dropped',
            'sendgrid_callback_handle_spamreport',
            'sendgrid_callback_handle_unsubscribe',
            'sendgrid_callback_handle_group_unsubscribe',
        ];

        foreach ($booleanFields as $field) {
            $config[$field] = !empty($config[$field]);
        }

        $allowedPolicies = ['auto', 'bounced', 'unsubscribed'];
        $policy          = (string) ($config['sendgrid_callback_dropped_policy'] ?? 'auto');
        if (!in_array($policy, $allowedPolicies, true)) {
            $policy = 'auto';
        }

        $config['sendgrid_callback_dropped_policy'] = $policy;

        $values['sendgridcallback_config'] = $config;
        $event->setConfig($values);
    }
}
