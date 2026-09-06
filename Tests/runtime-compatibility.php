<?php

declare(strict_types=1);

// MAUTIC_ROOT=/path/to/mautic php Tests/runtime-compatibility.php
// Install this plugin in the target Mautic before running the test.
ob_start();
$root = realpath(getenv('MAUTIC_ROOT') ?: '');
if (!$root || !is_file($root.'/vendor/autoload.php')) {
    throw new RuntimeException('Set MAUTIC_ROOT to an installed Mautic project.');
}
$bundle = 'SendgridCallbackBundle';
putenv('COMPAT_BUNDLE='.$bundle);
$cache = sys_get_temp_dir().'/plugin-compat-'.bin2hex(random_bytes(8));
putenv('COMPAT_CACHE='.$cache);
chdir($root);
$applicationDir = is_dir($root.'/docroot/app') ? $root.'/docroot' : $root;
define('IN_MAUTIC_CONSOLE', 1);
define('MAUTIC_ROOT_DIR', $applicationDir);
require $applicationDir.'/app/config/bootstrap.php';
register_shutdown_function(static function () use ($cache): void {
    (new Symfony\Component\Filesystem\Filesystem())->remove($cache);
});

class PluginCompatibilityKernel extends AppKernel
{
    public function getProjectDir(): string
    {
        return realpath(getenv('MAUTIC_ROOT'));
    }

    public function getCacheDir(): string
    {
        return getenv('COMPAT_CACHE');
    }

    public function build(Symfony\Component\DependencyInjection\ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new class implements Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface {
            public function process(Symfony\Component\DependencyInjection\ContainerBuilder $container): void
            {
                // Public only in this test container. No bypass of core final classes.
                $container->getDefinition('form.registry')->setPublic(true);
                $container->getDefinition('form.factory')->setPublic(true);
                $prefix = 'MauticPlugin\\'.getenv('COMPAT_BUNDLE').'\\';
                $services = $forms = [];
                foreach ($container->getDefinitions() as $id => $definition) {
                    if ($definition->isAbstract() || !str_starts_with((string) $definition->getClass(), $prefix)) {
                        continue;
                    }
                    if ($definition->hasTag('mautic.integration') || $definition->hasTag('mautic.basic_integration')
                        || $definition->hasTag('mautic.config_integration') || $definition->hasTag('form.type')) {
                        $definition->setPublic(true);
                        $services[] = $id;
                        if ($definition->hasTag('form.type')) {
                            $forms[] = $definition->getClass();
                        }
                    }
                }
                $container->setParameter('compat.services', $services);
                $container->setParameter('compat.forms', $forms);
            }
        }, Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION, -100);
    }
}

$kernel = new PluginCompatibilityKernel('prod', false);
$status = 0;
try {
    $kernel->boot();
    $container = $kernel->getContainer();
    $request = Symfony\Component\HttpFoundation\Request::create('https://compat.example.invalid/s/plugins');
    $request->setSession(new Symfony\Component\HttpFoundation\Session\Session(
        new Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage()
    ));
    $container->get('request_stack')->push($request);
    $bundles = $container->getParameter('mautic.plugin.bundles');
    if (!isset($bundles[$bundle])) {
        throw new RuntimeException('Plugin was not registered by Mautic.');
    }
    $services = array_unique(array_merge(
        $container->getParameter('compat.services'),
        array_keys($bundles[$bundle]['config']['services']['integrations'] ?? [])
    ));
    foreach ($services as $id) {
        // Compilation alone misses a class-name string injected instead of a service.
        $integration = $container->get($id);
        echo 'SERVICE '.$id.' '.get_class($integration).PHP_EOL;
        if (!$integration instanceof Mautic\PluginBundle\Integration\AbstractIntegration) {
            continue;
        }
        $settings = new Mautic\PluginBundle\Entity\Integration();
        $settings->setName($integration->getName());
        $settings->setIsPublished(false);
        $integration->setIntegrationSettings($settings);
        $form = $container->get('form.factory')->create(Mautic\PluginBundle\Form\Type\DetailsType::class, $settings, [
            'integration' => $integration->getName(),
            'integration_object' => $integration,
            'lead_fields' => [],
            'company_fields' => [],
            'csrf_protection' => false,
        ]);
        $form->createView();
        echo 'SETTINGS '.$integration->getName().PHP_EOL;
    }
    foreach ($container->getParameter('compat.forms') as $formType) {
        $container->get('form.registry')->getType($formType);
        echo 'FORM '.$formType.PHP_EOL;
    }
    echo 'PASS '.$bundle.' Mautic '.$kernel->getVersion().' PHP '.PHP_VERSION.PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, get_class($exception).': '.$exception->getMessage().PHP_EOL);
    $status = 1;
} finally {
    $kernel->shutdown();
}
exit($status);
