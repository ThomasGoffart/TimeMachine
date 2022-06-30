<?php

namespace NorthernIndustry\TimeMachineBundle\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use NorthernIndustry\TimeMachineBundle\EventListener\DatabaseSubscriber;

class TimeMachineExtension extends Extension {

	public function load(array $configs, ContainerBuilder $container): void {
		$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
		$loader->load('services.yaml');

		$schema = new Configuration();
		$options = $this->processConfiguration($schema, $configs);

		$container->setParameter('time_machine.configuration', $options);

		$eventListener = $container->getDefinition(DatabaseSubscriber::class);
		$eventListener->addMethodCall('setEnabled', [$options['enabled']]);
		$eventListener->addMethodCall('setEntities', [$options['entities']]);
		$eventListener->addMethodCall('setIgnoredColumns', [$options['ignored_columns']]);
	}

}