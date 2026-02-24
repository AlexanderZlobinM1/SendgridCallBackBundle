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

    private function createSubscriber(TransportCallback $transportCallback): CallbackSubscriber
    {
        $coreParametersHelper = $this->createMock(CoreParametersHelper::class);
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
