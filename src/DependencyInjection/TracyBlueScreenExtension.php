<?php

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class TracyBlueScreenExtension extends \Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension
{

	const CONTAINER_PARAMETER_CONTROLLER_LISTENER_PRIORITY = 'vasek_purchart.tracy_blue_screen.controller.listener_priority';

	/**
	 * @param mixed[] $mergedConfig
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	public function loadInternal(array $mergedConfig, ContainerBuilder $container)
	{
		$container->setParameter(
			self::CONTAINER_PARAMETER_CONTROLLER_LISTENER_PRIORITY,
			$mergedConfig[Configuration::SECTION_CONTROLLER][Configuration::PARAMETER_CONTROLLER_LISTENER_PRIORITY]
		);

		$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/config'));
		$loader->load('services.yml');

		$environment = $container->getParameter('kernel.environment');
		$debug = $container->getParameter('kernel.debug');

		if ($environment === 'dev' && $debug === true) {
			$loader->load('controller_listener.yml');
		}
	}

	/**
	 * @param mixed[] $config
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 * @return \VasekPurchart\TracyBlueScreenBundle\DependencyInjection\Configuration
	 */
	public function getConfiguration(array $config, ContainerBuilder $container)
	{
		return new Configuration(
			$this->getAlias()
		);
	}

}
