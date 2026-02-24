<?php

declare(strict_types=1);

namespace MauticPlugin\SendgridCallbackBundle\Form\Type;

use Mautic\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'sendgrid_callback_enabled',
            YesNoButtonGroupType::class,
            [
                'label' => 'sendgrid_callback.config.enabled',
                'data'  => $options['data']['sendgrid_callback_enabled'] ?? true,
                'attr'  => [
                    'tooltip' => 'sendgrid_callback.config.enabled.tooltip',
                ],
            ]
        );

        $builder->add(
            'sendgrid_callback_handle_bounce',
            CheckboxType::class,
            [
                'label'      => 'sendgrid_callback.config.handle_bounce',
                'required'   => false,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['tooltip' => 'sendgrid_callback.config.handle_bounce.tooltip'],
            ]
        );

        $builder->add(
            'sendgrid_callback_handle_blocked',
            CheckboxType::class,
            [
                'label'      => 'sendgrid_callback.config.handle_blocked',
                'required'   => false,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['tooltip' => 'sendgrid_callback.config.handle_blocked.tooltip'],
            ]
        );

        $builder->add(
            'sendgrid_callback_handle_dropped',
            CheckboxType::class,
            [
                'label'      => 'sendgrid_callback.config.handle_dropped',
                'required'   => false,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['tooltip' => 'sendgrid_callback.config.handle_dropped.tooltip'],
            ]
        );

        $builder->add(
            'sendgrid_callback_handle_spamreport',
            CheckboxType::class,
            [
                'label'      => 'sendgrid_callback.config.handle_spamreport',
                'required'   => false,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['tooltip' => 'sendgrid_callback.config.handle_spamreport.tooltip'],
            ]
        );

        $builder->add(
            'sendgrid_callback_handle_unsubscribe',
            CheckboxType::class,
            [
                'label'      => 'sendgrid_callback.config.handle_unsubscribe',
                'required'   => false,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['tooltip' => 'sendgrid_callback.config.handle_unsubscribe.tooltip'],
            ]
        );

        $builder->add(
            'sendgrid_callback_handle_group_unsubscribe',
            CheckboxType::class,
            [
                'label'      => 'sendgrid_callback.config.handle_group_unsubscribe',
                'required'   => false,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['tooltip' => 'sendgrid_callback.config.handle_group_unsubscribe.tooltip'],
            ]
        );

        $builder->add(
            'sendgrid_callback_dropped_policy',
            ChoiceType::class,
            [
                'label'       => 'sendgrid_callback.config.dropped_policy',
                'required'    => true,
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control', 'tooltip' => 'sendgrid_callback.config.dropped_policy.tooltip'],
                'choices'     => [
                    'sendgrid_callback.config.dropped_policy.auto'         => 'auto',
                    'sendgrid_callback.config.dropped_policy.bounced'      => 'bounced',
                    'sendgrid_callback.config.dropped_policy.unsubscribed' => 'unsubscribed',
                ],
                'placeholder' => false,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'sendgridcallback_config';
    }
}
