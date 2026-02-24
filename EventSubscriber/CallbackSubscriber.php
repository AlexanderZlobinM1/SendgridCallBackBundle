<?php

declare(strict_types=1);

namespace MauticPlugin\SendgridCallbackBundle\EventSubscriber;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\TransportWebhookEvent;
use Mautic\EmailBundle\Model\TransportCallback;
use Mautic\LeadBundle\Entity\DoNotContact;
use MauticPlugin\SendgridCallbackBundle\SendgridCallbackBundle;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mime\Address;

class CallbackSubscriber implements EventSubscriberInterface
{
    private const SUPPORTED_DROPPED_POLICIES = ['auto', 'bounced', 'unsubscribed'];

    public function __construct(
        private TransportCallback $transportCallback,
        private CoreParametersHelper $coreParametersHelper,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            EmailEvents::ON_TRANSPORT_WEBHOOK => 'processCallbackRequest',
        ];
    }

    public function processCallbackRequest(TransportWebhookEvent $event): void
    {
        if (!$this->isPluginEnabled() || !$this->isSupportedMailerScheme()) {
            return;
        }

        $payload = json_decode($event->getRequest()->getContent(), true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            $event->setResponse(new Response('Invalid JSON', Response::HTTP_BAD_REQUEST));

            return;
        }

        if (!is_array($payload)) {
            $event->setResponse(new Response('Invalid payload', Response::HTTP_BAD_REQUEST));

            return;
        }

        try {
            $processed = $this->processPayload($payload);
            $event->setResponse(new Response(sprintf('SendGrid Callback processed (%d)', $processed)));
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to process SendGrid payload: '.$exception->getMessage());
            $event->setResponse(new Response('Bad Request', Response::HTTP_BAD_REQUEST));
        }
    }

    private function isSupportedMailerScheme(): bool
    {
        $dsnString = (string) $this->coreParametersHelper->get('mailer_dsn');

        if ('' === $dsnString) {
            return false;
        }

        try {
            $dsn = Dsn::fromString($dsnString);

            if (in_array($dsn->getScheme(), SendgridCallbackBundle::SUPPORTED_MAILER_SCHEMES, true)) {
                return true;
            }
        } catch (\InvalidArgumentException) {
            // Fall back to string-based check for compound transports such as failover().
        }

        return (bool) preg_match('/(^|[(:])sendgrid(\+smtp|\+api)?:\/\//i', $dsnString);
    }

    /**
     * @param array<string, mixed>|array<int, array<string, mixed>> $payload
     */
    private function processPayload(array $payload): int
    {
        if ($this->isAssoc($payload)) {
            return $this->processEvent($payload);
        }

        $processed = 0;

        foreach ($payload as $item) {
            if (!is_array($item)) {
                continue;
            }

            $processed += $this->processEvent($item);
        }

        return $processed;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function processEvent(array $payload): int
    {
        $eventType = strtolower((string) ($payload['event'] ?? ''));
        $email     = (string) ($payload['email'] ?? '');

        if ('' === $eventType || '' === $email || !$this->isEventEnabled($eventType)) {
            return 0;
        }

        try {
            $address = Address::create($email)->getAddress();
            $emailId = $this->getEmailId($payload);
            $reason  = $this->buildReason($payload, $eventType);

            if (in_array($eventType, ['bounce', 'blocked'], true)) {
                $this->transportCallback->addFailureByAddress(
                    $address,
                    $reason,
                    DoNotContact::BOUNCED,
                    null !== $emailId ? (int) $emailId : null
                );

                $this->logger->info(sprintf('Processed SendGrid %s for %s', $eventType, $address));

                return 1;
            }

            if ('dropped' === $eventType) {
                $dncReason = $this->resolveDroppedReason($reason);
                $this->transportCallback->addFailureByAddress(
                    $address,
                    $reason,
                    $dncReason,
                    null !== $emailId ? (int) $emailId : null
                );

                $this->logger->info(sprintf('Processed SendGrid %s for %s', $eventType, $address));

                return 1;
            }

            if (in_array($eventType, ['spamreport', 'unsubscribe', 'group_unsubscribe'], true)) {
                $this->transportCallback->addFailureByAddress(
                    $address,
                    $reason,
                    DoNotContact::UNSUBSCRIBED,
                    null !== $emailId ? (int) $emailId : null
                );

                $this->logger->info(sprintf('Processed SendGrid %s for %s', $eventType, $address));

                return 1;
            }
        } catch (\Throwable $exception) {
            $this->logger->warning('Skipping invalid SendGrid event: '.$exception->getMessage());
        }

        return 0;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function buildReason(array $payload, string $fallback): string
    {
        $parts = [
            $payload['reason'] ?? null,
            $payload['status'] ?? null,
            $payload['type'] ?? null,
            $payload['response'] ?? null,
        ];

        $parts = array_values(array_filter(array_map(static fn ($value): ?string => is_scalar($value) && '' !== (string) $value ? (string) $value : null, $parts)));

        if ([] === $parts) {
            return $fallback;
        }

        return implode(' | ', $parts);
    }

    private function resolveDroppedReason(string $reason): int
    {
        $policy = strtolower((string) ($this->coreParametersHelper->get('sendgrid_callback_dropped_policy') ?? 'auto'));
        if (!in_array($policy, self::SUPPORTED_DROPPED_POLICIES, true)) {
            $policy = 'auto';
        }

        if ('bounced' === $policy) {
            return DoNotContact::BOUNCED;
        }

        if ('unsubscribed' === $policy) {
            return DoNotContact::UNSUBSCRIBED;
        }

        $normalizedReason = strtolower($reason);
        if (str_contains($normalizedReason, 'unsubscribe') || str_contains($normalizedReason, 'spam')) {
            return DoNotContact::UNSUBSCRIBED;
        }

        return DoNotContact::BOUNCED;
    }

    private function isEventEnabled(string $eventType): bool
    {
        $parameterMap = [
            'bounce'            => 'sendgrid_callback_handle_bounce',
            'blocked'           => 'sendgrid_callback_handle_blocked',
            'dropped'           => 'sendgrid_callback_handle_dropped',
            'spamreport'        => 'sendgrid_callback_handle_spamreport',
            'unsubscribe'       => 'sendgrid_callback_handle_unsubscribe',
            'group_unsubscribe' => 'sendgrid_callback_handle_group_unsubscribe',
        ];

        if (!isset($parameterMap[$eventType])) {
            return false;
        }

        return $this->toBoolean($this->coreParametersHelper->get($parameterMap[$eventType]), true);
    }

    private function isPluginEnabled(): bool
    {
        return $this->toBoolean($this->coreParametersHelper->get('sendgrid_callback_enabled'), true);
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

    /**
     * @param array<string, mixed> $payload
     */
    private function getEmailId(array $payload): ?string
    {
        foreach (['custom_args', 'unique_args'] as $argKey) {
            if (!isset($payload[$argKey]) || !is_array($payload[$argKey])) {
                continue;
            }

            foreach ($payload[$argKey] as $key => $value) {
                if (0 === strcasecmp((string) $key, 'X-EMAIL-ID') && is_scalar($value)) {
                    $emailId = (string) $value;

                    return ctype_digit($emailId) ? $emailId : null;
                }
            }
        }

        if (isset($payload['smtp-id']) && is_scalar($payload['smtp-id'])) {
            $smtpId = (string) $payload['smtp-id'];
            if (preg_match('/X-EMAIL-ID[:=]([0-9]+)/i', $smtpId, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * @param array<mixed> $value
     */
    private function isAssoc(array $value): bool
    {
        return array_keys($value) !== range(0, count($value) - 1);
    }
}
