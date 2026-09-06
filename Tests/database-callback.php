<?php

declare(strict_types=1);
// Creates and removes only its own contact/email fixtures. No external messages are sent.
$root = realpath(getenv('MAUTIC_ROOT') ?: '');
if (!$root) {
    throw new RuntimeException('Set MAUTIC_ROOT.');
}
$bundle = 'SendgridCallbackBundle';
$web = is_dir($root.'/docroot') ? $root.'/docroot' : $root;
chdir($root);
define('IN_MAUTIC_CONSOLE', 1);
define('MAUTIC_ROOT_DIR', $web);
require $web.'/app/config/bootstrap.php';
$cache = sys_get_temp_dir().'/callback-db-'.bin2hex(random_bytes(8));
putenv('CALLBACK_CACHE='.$cache);
class CallbackDatabaseKernel extends AppKernel
{
    public function getProjectDir(): string
    {
        return realpath(getenv('MAUTIC_ROOT'));
    }

    public function getCacheDir(): string
    {
        return getenv('CALLBACK_CACHE');
    }

    public function build(Symfony\Component\DependencyInjection\ContainerBuilder $c): void
    {
        parent::build($c);
        $c->addCompilerPass(new class implements Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface {
            public function process(Symfony\Component\DependencyInjection\ContainerBuilder $c): void
            {
                foreach ($c->getDefinitions() as $id => $d) {
                    foreach (['callback_test.model' => Mautic\EmailBundle\Model\TransportCallback::class, 'callback_test.integration' => Mautic\PluginBundle\Helper\IntegrationHelper::class] as $alias => $class) {
                        if ($class === $d->getClass()) {
                            $d->setPublic(true);
                            $c->setAlias($alias, $id)->setPublic(true);
                        }
                    }
                }
            }
        });
    }
}
$kernel = new CallbackDatabaseKernel('prod', false);
$em = null;
$leadId = $emailId = $testPluginId = null;
try {
    $kernel->boot();
    $c = $kernel->getContainer();
    $em = $c->get('doctrine.orm.entity_manager');
    $db = $em->getConnection();
    $prefix = $c->getParameter('mautic.db_table_prefix');
    $lead = new Mautic\LeadBundle\Entity\Lead();
    $address = 'callback-fixture-'.bin2hex(random_bytes(8)).'@example.invalid';
    $lead->setEmail($address);
    $email = new Mautic\EmailBundle\Entity\Email();
    $email->setName('Callback isolated fixture');
    $email->setSubject('Callback isolated fixture');
    $email->setEmailType('template');
    $email->setCustomHtml('<p>Fixture</p>');
    $email->setLanguage('en');
    $em->persist($lead);
    $em->persist($email);
    $em->flush();
    $leadId = $lead->getId();
    $emailId = $email->getId();
    $existingPlugin = $em->getRepository(Mautic\PluginBundle\Entity\Plugin::class)->findOneBy(['bundle' => $bundle]);
    if (!$existingPlugin) {
        $plugin = new Mautic\PluginBundle\Entity\Plugin();
        $plugin->setBundle($bundle);
        $plugin->setName($bundle);
        $plugin->setVersion('test');
        $c->get('mautic.plugin.model.plugin')->saveEntity($plugin);
        $testPluginId = $plugin->getId();
    }
    $helper = $c->get('callback_test.integration');
    $name = match ($bundle) {
        'MailganerBundle' => 'Mailganer','MailganerCallbackBundle' => 'MailganerCallback',default => 'SendgridCallback',
    };
    $integration = $helper->getIntegrationObject($name);
    $settings = new Mautic\PluginBundle\Entity\Integration();
    $settings->setName($name);
    $settings->setIsPublished(true);
    $integration->setIntegrationSettings($settings);
    $dsn = match ($bundle) {
        'MailganerBundle' => 'mailganer+api://fixture@default','MailganerCallbackBundle' => 'smtp://fixture:fixture@api.samotpravil.ru:1126',default => 'sendgrid+api://fixture@default',
    };
    $parameters = new Mautic\CoreBundle\Helper\CoreParametersHelper(new Symfony\Component\DependencyInjection\Container(new Symfony\Component\DependencyInjection\ParameterBag\ParameterBag(['mautic.mailer_dsn' => $dsn])));
    $class = 'MauticPlugin\\'.$bundle.'\\EventSubscriber\\CallbackSubscriber';
    $subscriber = new $class($c->get('callback_test.model'), $parameters, $helper, new Psr\Log\NullLogger(), $c->get('MauticPlugin\\'.$bundle.'\\Model\\DncFeedback'));
    $data = $bundle === 'SendgridCallbackBundle' ? ['event' => 'bounce', 'email' => $address, 'X-EMAIL-ID' => (string) $emailId] : ['xml_messages' => [['status' => 'failed', 'email' => $address, 'external_id' => 'mtc-e'.$emailId.'-pfixture']]];
    $em->clear(); // Match a fresh webhook request loading pre-existing database contacts.
    for ($i = 0; $i < 2; ++$i) {
        $request = Symfony\Component\HttpFoundation\Request::create('/mailer/callback', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        $event = new Mautic\EmailBundle\Event\TransportWebhookEvent($request);
        $subscriber->processCallbackRequest($event);
        if (200 !== $event->getResponse()?->getStatusCode()) {
            throw new RuntimeException('Callback did not succeed.');
        }
    }
    $rows = $db->fetchAllAssociative('SELECT channel,channel_id,reason FROM '.$prefix.'lead_donotcontact WHERE lead_id=?', [$leadId]);
    if (count($rows) !== 1 || $rows[0]['channel'] !== 'email' || (int) $rows[0]['channel_id'] !== $emailId || (int) $rows[0]['reason'] !== 2) {
        throw new RuntimeException('Unexpected DNC attribution: '.json_encode($rows));
    }
    $data = $bundle === 'SendgridCallbackBundle' ? ['event' => 'unsubscribe', 'email' => $address, 'X-EMAIL-ID' => (string) $emailId] : ['messages' => [['status' => 'unsubscribe', 'email' => $address, 'x_track_id' => 'mtc-e'.$emailId.'-hfixture']]];
    $event = new Mautic\EmailBundle\Event\TransportWebhookEvent(Symfony\Component\HttpFoundation\Request::create('/mailer/callback', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data)));
    $subscriber->processCallbackRequest($event);
    $rows = $db->fetchAllAssociative('SELECT channel,channel_id,reason FROM '.$prefix.'lead_donotcontact WHERE lead_id=?', [$leadId]);
    if (200 !== $event->getResponse()?->getStatusCode() || count($rows) !== 1 || (int) $rows[0]['reason'] !== 1 || (int) $rows[0]['channel_id'] !== $emailId) {
        throw new RuntimeException('Failed to upgrade bounce to unsubscribe without duplicate rows: '.json_encode($rows));
    }
    echo 'PASS '.$bundle.' real database attribution + duplicate webhook on Mautic '.$kernel->getVersion().PHP_EOL;
} finally {
    if ($em && $leadId) {
        $db->delete($prefix.'lead_donotcontact', ['lead_id' => $leadId]);
        $db->delete($prefix.'leads', ['id' => $leadId]);
    }
    if ($em && $emailId) {
        $db->delete($prefix.'emails', ['id' => $emailId]);
    }
    if ($em && $testPluginId) {
        $db->delete($prefix.'plugin_integration_settings', ['plugin_id' => $testPluginId]);
        $db->delete($prefix.'plugins', ['id' => $testPluginId]);
    }
    $kernel->shutdown();
    (new Symfony\Component\Filesystem\Filesystem())->remove($cache);
}
