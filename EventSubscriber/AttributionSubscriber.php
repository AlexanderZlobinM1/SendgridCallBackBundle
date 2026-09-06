<?php

declare(strict_types=1);

namespace MauticPlugin\SendgridCallbackBundle\EventSubscriber;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mime\Email;

final class AttributionSubscriber implements EventSubscriberInterface
{
    public function __construct(private CoreParametersHelper $parameters, private IntegrationHelper $integrations)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [MessageEvent::class => 'prepare'];
    }

    public function prepare(MessageEvent $event): void
    {
        $integration = $this->integrations->getIntegrationObject('SendgridCallback');
        if (!$integration || !$integration->getIntegrationSettings()?->getIsPublished()) {
            return;
        }
        $dsn = (string) $this->parameters->get('mailer_dsn');
        if (!preg_match('/(^|[(:])sendgrid(\\+smtp|\\+api)?:\\/\\//i', $dsn)) {
            return;
        }
        $email = $event->getMessage();
        if (!$email instanceof Email) {
            return;
        }
        $id = $email->getHeaders()->get('X-EMAIL-ID')?->getBodyAsString();
        if (false === filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            return;
        }
        $headers = $email->getHeaders();
        $found = false;
        foreach ($headers->all() as $header) {
            if ($header instanceof MetadataHeader && 'X-EMAIL-ID' === $header->getKey()) {
                $found = true;
            }
        }
        if (!$found) {
            $headers->add(new MetadataHeader('X-EMAIL-ID', (string) $id));
        }
        $old = $headers->get('X-SMTPAPI');
        $data = null !== $old ? json_decode($old->getBodyAsString(), true, 512, JSON_THROW_ON_ERROR) : [];
        if (!is_array($data)) {
            throw new \RuntimeException('Invalid SendGrid X-SMTPAPI metadata.');
        }
        $data['unique_args']['X-EMAIL-ID'] = (string) $id;
        $headers->remove('X-SMTPAPI');
        $headers->addTextHeader('X-SMTPAPI', json_encode($data, JSON_THROW_ON_ERROR));
    }
}
