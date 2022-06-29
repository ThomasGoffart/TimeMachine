<?php

namespace NorthernIndustry\TimeMachineBundle\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use NorthernIndustry\TimeMachineBundle\EventListener\DatabaseSubscriber;

class TimeMachineExtension extends Extension {

	public function load(array $configs, ContainerBuilder $container): void {
		//		$configuration = new Configuration();
		//		$config = $this->processConfiguration($configuration, $configs);

		$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
		$loader->load('services.yaml');

		//		$auditorConfig = $config;
		//		unset($auditorConfig['providers']);
		//		$container->setParameter('dh_auditor.configuration', $auditorConfig);
		//
		//		$this->loadProviders($container, $config);
		//
		//		$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../Resources/config'));
		//		$loader->load('services/services.yaml');
		//
		$schema = new Configuration();
		$options = $this->processConfiguration($schema, $configs);

		$eventListener = $container->getDefinition(DatabaseSubscriber::class);
		$eventListener->addMethodCall('setEnabled', [$options['enabled']]);
		$eventListener->addMethodCall('setEntities', [$options['entities']]);
		$eventListener->addMethodCall('setIgnoredColumns', [$options['ignored_columns']]);
	}

	private function loadProviders(ContainerBuilder $container, array $config): void {
		//		dd($config);
		//
		//		foreach ($config['providers'] as $providerName => $providerConfig) {
		//			$container->setParameter('dh_auditor.provider.'.$providerName.'.configuration', $providerConfig);
		//
		//			if (method_exists($container, 'registerAliasForArgument')) {
		//				$serviceId = 'dh_auditor.provider.'.$providerName;
		//				$container->registerAliasForArgument($serviceId, ProviderInterface::class, "{$providerName}Provider");
		//			}
		//		}
	}

}