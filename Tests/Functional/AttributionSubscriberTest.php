<?php

declare(strict_types=1);

namespace MauticPlugin\SendgridCallbackBundle\Tests\Functional;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\PluginBundle\Entity\Integration;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Mautic\PluginBundle\Integration\AbstractIntegration;
use MauticPlugin\SendgridCallbackBundle\EventSubscriber\AttributionSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mime\Email;

final class AttributionSubscriberTest extends TestCase
{
    private function subscriber(bool $enabled = true): AttributionSubscriber
    {
        $p = $this->createMock(CoreParametersHelper::class);
        $p->method('get')->willReturn('sendgrid+api://key@default');
        $settings = new Integration();
        $settings->setIsPublished($enabled);
        $integration = $this->createMock(AbstractIntegration::class);
        $integration->method('getIntegrationSettings')->willReturn($settings);
        $h = $this->createMock(IntegrationHelper::class);
        $h->method('getIntegrationObject')->willReturn($integration);

        return new AttributionSubscriber($p, $h);
    }

    public function testEmailIdSurvivesBothApiAndSmtpWithoutErasingCustomMetadata(): void
    {
        $e = (new Email())->from('sender@example.com')->to('recipient@example.com')->subject('test')->html('test');
        $e->getHeaders()->addTextHeader('X-EMAIL-ID', '42');
        $e->getHeaders()->addTextHeader('X-SMTPAPI', '{"category":["existing"],"unique_args":{"custom":"keep"}}');
        $event = new MessageEvent($e, new Envelope($e->getFrom()[0], $e->getTo()), 'sendgrid+api');
        $s = $this->subscriber();
        $s->prepare($event);
        $s->prepare($event);
        $data = json_decode($e->getHeaders()->get('X-SMTPAPI')->getBodyAsString(), true);
        self::assertSame(['existing'], $data['category']);
        self::assertSame(['custom' => 'keep', 'X-EMAIL-ID' => '42'], $data['unique_args']);
        $metadata = array_filter(iterator_to_array($e->getHeaders()->all()), fn ($h) => $h instanceof MetadataHeader);
        self::assertCount(1, $metadata);
        self::assertSame('42', array_values($metadata)[0]->getValue());
    }

    public function testDisabledIntegrationDoesNotModifyMessages(): void
    {
        $e = (new Email())->from('sender@example.com')->to('recipient@example.com')->subject('test')->html('test');
        $e->getHeaders()->addTextHeader('X-EMAIL-ID', '42');
        $this->subscriber(false)->prepare(new MessageEvent($e, new Envelope($e->getFrom()[0], $e->getTo()), 'sendgrid+api'));
        self::assertFalse($e->getHeaders()->has('X-SMTPAPI'));
    }
}
