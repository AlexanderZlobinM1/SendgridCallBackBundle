<?php

declare(strict_types=1);

namespace MauticPlugin\SendgridCallbackBundle\Integration;

use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use Mautic\PluginBundle\Integration\AbstractIntegration;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SendgridCallbackIntegration extends AbstractIntegration
{
    public const INTEGRATION_NAME = 'SendgridCallback';

    public function getName(): string
    {
        return self::INTEGRATION_NAME;
    }

    public function getDisplayName(): string
    {
        return 'SendGrid Callback';
    }

    public function getIcon(): string
    {
        return 'plugins/SendgridCallbackBundle/Assets/img/icon.png';
    }

    public function getAuthenticationType(): string
    {
        return 'none';
    }

    /**
     * @return array<string, string>
     */
    public function getRequiredKeyFields(): array
    {
        return [];
    }

    /**
     * @param mixed $builder
     * @param array<string, mixed> $data
     */
    public function appendToForm(&$builder, $data, $formArea): void
    {
        if ('keys' !== $formArea) {
            return;
        }

        $builder->add('sendgrid_callback_handle_bounce', YesNoButtonGroupType::class, [
            'label' => 'sendgrid_callback.config.handle_bounce',
            'data'  => $this->toBool($data['sendgrid_callback_handle_bounce'] ?? true),
            'row_attr' => [
                'style' => 'margin-top: 30px;',
            ],
            'attr'  => [
                'tooltip' => 'sendgrid_callback.config.handle_bounce.tooltip',
            ],
        ]);

        $builder->add('sendgrid_callback_handle_blocked', YesNoButtonGroupType::class, [
            'label' => 'sendgrid_callback.config.handle_blocked',
            'data'  => $this->toBool($data['sendgrid_callback_handle_blocked'] ?? true),
            'attr'  => ['tooltip' => 'sendgrid_callback.config.handle_blocked.tooltip'],
        ]);

        $builder->add('sendgrid_callback_handle_dropped', YesNoButtonGroupType::class, [
            'label' => 'sendgrid_callback.config.handle_dropped',
            'data'  => $this->toBool($data['sendgrid_callback_handle_dropped'] ?? true),
            'attr'  => ['tooltip' => 'sendgrid_callback.config.handle_dropped.tooltip'],
        ]);

        $builder->add('sendgrid_callback_handle_spamreport', YesNoButtonGroupType::class, [
            'label' => 'sendgrid_callback.config.handle_spamreport',
            'data'  => $this->toBool($data['sendgrid_callback_handle_spamreport'] ?? true),
            'attr'  => ['tooltip' => 'sendgrid_callback.config.handle_spamreport.tooltip'],
        ]);

        $builder->add('sendgrid_callback_handle_unsubscribe', YesNoButtonGroupType::class, [
            'label' => 'sendgrid_callback.config.handle_unsubscribe',
            'data'  => $this->toBool($data['sendgrid_callback_handle_unsubscribe'] ?? true),
            'attr'  => ['tooltip' => 'sendgrid_callback.config.handle_unsubscribe.tooltip'],
        ]);

        $builder->add('sendgrid_callback_handle_group_unsubscribe', YesNoButtonGroupType::class, [
            'label' => 'sendgrid_callback.config.handle_group_unsubscribe',
            'data'  => $this->toBool($data['sendgrid_callback_handle_group_unsubscribe'] ?? true),
            'attr'  => ['tooltip' => 'sendgrid_callback.config.handle_group_unsubscribe.tooltip'],
        ]);

        $builder->add('sendgrid_callback_dropped_policy', ChoiceType::class, [
            'label'       => 'sendgrid_callback.config.dropped_policy',
            'required'    => true,
            'placeholder' => false,
            'data'        => (string) ($data['sendgrid_callback_dropped_policy'] ?? 'auto'),
            'choices'     => [
                'sendgrid_callback.config.dropped_policy.auto'         => 'auto',
                'sendgrid_callback.config.dropped_policy.bounced'      => 'bounced',
                'sendgrid_callback.config.dropped_policy.unsubscribed' => 'unsubscribed',
            ],
            'label_attr'  => ['class' => 'control-label'],
            'attr'        => [
                'class'   => 'form-control',
                'tooltip' => 'sendgrid_callback.config.dropped_policy.tooltip',
            ],
        ]);

    }

    /**
     * @return string|array<string, mixed>
     */
    public function getFormNotes($section)
    {
        if ('custom' === $section) {
            return [
                'custom'     => true,
                'template'   => '@SendgridCallback/Integration/footer.html.twig',
                'parameters' => [],
            ];
        }

        return parent::getFormNotes($section);
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }
}
