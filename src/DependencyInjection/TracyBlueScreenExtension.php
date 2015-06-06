<?php

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class TracyBlueScreenExtension extends \Symfony\Component\HttpKernel\DependencyInjection\Extension
{

	/**
	 * @param mixed[][] $configs
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/config'));
		$loader->load('services.yml');

		$environment = $container->getParameter('kernel.environment');
		$debug = $container->getParameter('kernel.debug');

		if ($environment === 'dev' && $debug === true) {
			$loader->load('controller_listener.yml');
		}
	}

}
