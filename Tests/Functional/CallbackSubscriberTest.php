<?php

declare(strict_types=1);

namespace MauticPlugin\SendgridCallbackBundle\Tests\Functional\EventSubscriber;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\EmailBundle\Model\TransportCallback;
use Mautic\LeadBundle\Entity\DoNotContact;
use MauticPlugin\SendgridCallbackBundle\EventSubscriber\CallbackSubscriber;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CallbackSubscriberTest extends TestCase
{
    public function testProcessBounceAddsBouncedDnc(): void
    {
        $transportCallback = $this->createMock(TransportCallback::class);
        $transportCallback
            ->expects(self::once())
            ->method('addFailureByAddress')
            ->with(
                'john.doe@example.com',
                self::stringContains('mailbox not found'),
                DoNotContact::BOUNCED,
                42
            );

        $subscriber = $this->createSubscriber($transportCallback);

        $eventPayload = [
            'event'       => 'bounce',
            'email'       => 'john.doe@example.com',
            'reason'      => 'mailbox not found',
            'status'      => '5.1.1',
            'custom_args' => ['X-EMAIL-ID' => '42'],
        ];

        self::assertSame(1, $this->invokeProcessPayload($subscriber, [$eventPayload]));
    }

    public function testProcessUnsubscribeAddsUnsubscribedDnc(): void
    {
        $transportCallback = $this->createMock(TransportCallback::class);
        $transportCallback
            ->expects(self::once())
            ->method('addFailureByAddress')
            ->with(
                'john.doe@example.com',
                self::stringContains('unsubscribe'),
                DoNotContact::UNSUBSCRIBED,
                null
            );

        $subscriber = $this->createSubscriber($transportCallback);

        $eventPayload = [
            'event' => 'unsubscribe',
            'email' => 'john.doe@example.com',
            'type'  => 'unsubscribe',
        ];

        self::assertSame(1, $this->invokeProcessPayload($subscriber, [$eventPayload]));
    }

    public function testProcessOpenEventIgnored(): void
    {
        $transportCallback = $this->createMock(TransportCallback::class);
        $transportCallback
            ->expects(self::never())
            ->method('addFailureByAddress');

        $subscriber = $this->createSubscriber($transportCallback);

        $eventPayload = [
            'event' => 'open',
            'email' => 'john.doe@example.com',
        ];

        self::assertSame(0, $this->invokeProcessPayload($subscriber, [$eventPayload]));
    }

    public function testDisabledBounceEventIgnored(): void
    {
        $transportCallback = $this->createMock(TransportCallback::class);
        $transportCallback
            ->expects(self::never())
            ->method('addFailureByAddress');

        $subscriber = $this->createSubscriber($transportCallback, [
            'sendgrid_callback_handle_bounce' => false,
        ]);

        $eventPayload = [
            'event'  => 'bounce',
            'email'  => 'john.doe@example.com',
            'reason' => 'mailbox not found',
        ];

        self::assertSame(0, $this->invokeProcessPayload($subscriber, [$eventPayload]));
    }

    public function testDroppedPolicyCanForceUnsubscribed(): void
    {
        $transportCallback = $this->createMock(TransportCallback::class);
        $transportCallback
            ->expects(self::once())
            ->method('addFailureByAddress')
            ->with(
                'john.doe@example.com',
                self::stringContains('mailbox full'),
                DoNotContact::UNSUBSCRIBED,
                null
            );

        $subscriber = $this->createSubscriber($transportCallback, [
            'sendgrid_callback_dropped_policy' => 'unsubscribed',
        ]);

        $eventPayload = [
            'event'  => 'dropped',
            'email'  => 'john.doe@example.com',
            'reason' => 'mailbox full',
        ];

        self::assertSame(1, $this->invokeProcessPayload($subscriber, [$eventPayload]));
    }

    public function testDisabledPluginSkipsAllEvents(): void
    {
        $transportCallback = $this->createMock(TransportCallback::class);
        $transportCallback
            ->expects(self::never())
            ->method('addFailureByAddress');

        $subscriber = $this->createSubscriber($transportCallback, [
            'sendgrid_callback_enabled' => false,
        ]);

        $eventPayload = [
            'event' => 'bounce',
            'email' => 'john.doe@example.com',
        ];

        self::assertSame(0, $this->invokeProcessPayload($subscriber, [$eventPayload]));
    }

    /**
     * @param array<string, mixed> $config
     */
    private function createSubscriber(TransportCallback $transportCallback, array $config = []): CallbackSubscriber
    {
        $coreParametersHelper = $this->createMock(CoreParametersHelper::class);
        $coreParametersHelper
            ->method('get')
            ->willReturnCallback(static fn (string $key, mixed $default = null) => $config[$key] ?? $default);

        $logger               = $this->createMock(LoggerInterface::class);

        return new CallbackSubscriber($transportCallback, $coreParametersHelper, $logger);
    }

    /**
     * @param array<int, array<string, mixed>> $payload
     */
    private function invokeProcessPayload(CallbackSubscriber $subscriber, array $payload): int
    {
        $reflection = new \ReflectionMethod($subscriber, 'processPayload');
        $reflection->setAccessible(true);

        /** @var int $result */
        $result = $reflection->invoke($subscriber, $payload);

        return $result;
    }
}
