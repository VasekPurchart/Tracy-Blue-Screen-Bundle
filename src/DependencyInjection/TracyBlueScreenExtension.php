<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class TracyBlueScreenExtension extends \Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension
{

	const CONTAINER_PARAMETER_BLUE_SCREEN_COLLAPSE_PATHS = 'vasek_purchart.tracy_blue_screen.blue_screen.collapse_paths';
	const CONTAINER_PARAMETER_CONSOLE_BROWSER = 'vasek_purchart.tracy_blue_screen.console.browser';
	const CONTAINER_PARAMETER_CONSOLE_LISTENER_PRIORITY = 'vasek_purchart.tracy_blue_screen.console.listener_priority';
	const CONTAINER_PARAMETER_CONSOLE_LOG_DIRECTORY = 'vasek_purchart.tracy_blue_screen.console.log_directory';
	const CONTAINER_PARAMETER_CONTROLLER_LISTENER_PRIORITY = 'vasek_purchart.tracy_blue_screen.controller.listener_priority';

	/**
	 * @param mixed[] $mergedConfig
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	public function loadInternal(array $mergedConfig, ContainerBuilder $container)
	{
		$container->setParameter(
			self::CONTAINER_PARAMETER_BLUE_SCREEN_COLLAPSE_PATHS,
			$mergedConfig[Configuration::SECTION_BLUE_SCREEN][Configuration::PARAMETER_COLLAPSE_PATHS]
		);
		$container->setParameter(
			self::CONTAINER_PARAMETER_CONSOLE_BROWSER,
			$mergedConfig[Configuration::SECTION_CONSOLE][Configuration::PARAMETER_CONSOLE_BROWSER]
		);
		$container->setParameter(
			self::CONTAINER_PARAMETER_CONSOLE_LISTENER_PRIORITY,
			$mergedConfig[Configuration::SECTION_CONSOLE][Configuration::PARAMETER_CONSOLE_LISTENER_PRIORITY]
		);
		$container->setParameter(
			self::CONTAINER_PARAMETER_CONSOLE_LOG_DIRECTORY,
			$mergedConfig[Configuration::SECTION_CONSOLE][Configuration::PARAMETER_CONSOLE_LOG_DIRECTORY]
		);
		$container->setParameter(
			self::CONTAINER_PARAMETER_CONTROLLER_LISTENER_PRIORITY,
			$mergedConfig[Configuration::SECTION_CONTROLLER][Configuration::PARAMETER_CONTROLLER_LISTENER_PRIORITY]
		);

		$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/config'));
		$loader->load('services.yml');

		$environment = $container->getParameter('kernel.environment');
		$debug = $container->getParameter('kernel.debug');

		if ($this->isEnabled(
			$mergedConfig[Configuration::SECTION_CONSOLE][Configuration::PARAMETER_CONSOLE_ENABLED],
			$environment,
			$debug
		)) {
			$loader->load('console_listener.yml');
		}
		if ($this->isEnabled(
			$mergedConfig[Configuration::SECTION_CONTROLLER][Configuration::PARAMETER_CONTROLLER_ENABLED],
			$environment,
			$debug
		)) {
			$loader->load('controller_listener.yml');
		}
	}

	/**
	 * @param mixed[] $config
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 * @return \VasekPurchart\TracyBlueScreenBundle\DependencyInjection\Configuration
	 */
	public function getConfiguration(array $config, ContainerBuilder $container): Configuration
	{
		return new Configuration(
			$this->getAlias(),
			$container->getParameter('kernel.root_dir'),
			$container->getParameter('kernel.logs_dir'),
			$container->getParameter('kernel.cache_dir')
		);
	}

	/**
	 * @param bool|null $configOption
	 * @param string $environment
	 * @param bool $debug
	 * @return bool
	 */
	private function isEnabled($configOption, string $environment, bool $debug): bool
	{
		if ($configOption === null) {
			return $environment === 'dev' && $debug === true;
		}

		return $configOption;
	}

}
