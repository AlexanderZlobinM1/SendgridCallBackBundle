<?php

declare(strict_types=1);

namespace MauticPlugin\SendgridCallbackBundle\Controller;

use Mautic\CoreBundle\Controller\CommonController;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Symfony\Component\HttpFoundation\Response;

class SettingsController extends CommonController
{
    public function indexAction(CoreParametersHelper $coreParametersHelper): Response
    {
        if (!$this->user->isAdmin()) {
            return $this->accessDenied();
        }

        $settings = [
            'handle_bounce'            => $this->toBoolean($coreParametersHelper->get('sendgrid_callback_handle_bounce'), true),
            'handle_blocked'           => $this->toBoolean($coreParametersHelper->get('sendgrid_callback_handle_blocked'), true),
            'handle_dropped'           => $this->toBoolean($coreParametersHelper->get('sendgrid_callback_handle_dropped'), true),
            'handle_spamreport'        => $this->toBoolean($coreParametersHelper->get('sendgrid_callback_handle_spamreport'), true),
            'handle_unsubscribe'       => $this->toBoolean($coreParametersHelper->get('sendgrid_callback_handle_unsubscribe'), true),
            'handle_group_unsubscribe' => $this->toBoolean($coreParametersHelper->get('sendgrid_callback_handle_group_unsubscribe'), true),
            'dropped_policy'           => (string) ($coreParametersHelper->get('sendgrid_callback_dropped_policy') ?? 'auto'),
        ];

        return $this->delegateView([
            'viewParameters' => [
                'settings' => $settings,
            ],
            'contentTemplate' => '@SendgridCallback/Settings/index.html.twig',
            'passthroughVars' => [
                'activeLink'    => '#sendgrid_callback_settings',
                'mauticContent' => 'sendgrid_callback_settings',
                'route'         => $this->generateUrl('sendgrid_callback_settings'),
            ],
        ]);
    }

    private function toBoolean(mixed $value, bool $default = false): bool
    {
        if (null === $value) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return 1 === $value;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'no', 'off', ''], true)) {
                return false;
            }
        }

        return $default;
    }
}
