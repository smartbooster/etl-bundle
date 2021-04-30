<?php

use Psr\Log\NullLogger;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new DAMA\DoctrineTestBundle\DAMADoctrineTestBundle(),
            new Liip\TestFixturesBundle\LiipTestFixturesBundle(),
            new Nelmio\Alice\Bridge\Symfony\NelmioAliceBundle(),
            new Fidry\AliceDataFixtures\Bridge\Symfony\FidryAliceDataFixturesBundle()
        ];

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__) . '/../var/cache';
    }

    public function getLogDir()
    {
        return dirname(__DIR__) . '/../var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->setParameter('container.autowiring.strict_mode', true);
            $container->setParameter('container.dumper.inline_class_loader', true);

            $container->addObjectResource($this);
        });
        $loader->load($this->getRootDir() . '/config_test.yml');
    }

    //https://github.com/dmaicher/doctrine-test-bundle/blob/master/tests/Functional/app/AppKernel.php
    protected function build(ContainerBuilder $container): void
    {
        $container->register('logger', NullLogger::class);
        $container->addCompilerPass(new class() implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                // until https://github.com/doctrine/DoctrineBundle/pull/1263 is released on 1.12.x as well
                $container->getDefinition('doctrine.dbal.logger.chain.default')->removeMethodCall('addLogger');
                $container->getDefinition('doctrine.dbal.logger.chain')->removeMethodCall('addLogger');
            }
        });
    }
}
