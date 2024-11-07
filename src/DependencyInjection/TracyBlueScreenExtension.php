<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class TracyBlueScreenExtension
	extends \Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension
{

	/**
	 * @param mixed[] $mergedConfig
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
	{
		$environment = $container->getParameter('kernel.environment');
		$debug = $container->getParameter('kernel.debug');

		$container->setParameter(
			'vasek_purchart.tracy_blue_screen.blue_screen.collapse_paths',
			$mergedConfig[Configuration::SECTION_BLUE_SCREEN][Configuration::PARAMETER_COLLAPSE_PATHS]
		);
		$container->setParameter(
			'vasek_purchart.tracy_blue_screen.console.browser',
			$mergedConfig[Configuration::SECTION_CONSOLE][Configuration::PARAMETER_CONSOLE_BROWSER]
		);
		$container->setParameter(
			'vasek_purchart.tracy_blue_screen.console.listener_priority',
			$mergedConfig[Configuration::SECTION_CONSOLE][Configuration::PARAMETER_CONSOLE_LISTENER_PRIORITY]
		);
		$container->setParameter(
			'vasek_purchart.tracy_blue_screen.console.log_directory',
			$mergedConfig[Configuration::SECTION_CONSOLE][Configuration::PARAMETER_CONSOLE_LOG_DIRECTORY]
		);
		$container->setParameter(
			'vasek_purchart.tracy_blue_screen.controller.enabled',
			$this->isEnabled(
				$mergedConfig[Configuration::SECTION_CONTROLLER][Configuration::PARAMETER_CONTROLLER_ENABLED],
				$environment,
				$debug
			)
		);

		$loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/config'));
		$loader->load('services.yaml');

		if ($this->isEnabled(
			$mergedConfig[Configuration::SECTION_CONSOLE][Configuration::PARAMETER_CONSOLE_ENABLED],
			$environment,
			$debug
		)) {
			$loader->load('console_listener.yaml');
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

	private function isEnabled(?bool $configOption, string $environment, bool $debug): bool
	{
		if ($configOption === null) {
			return $environment === 'dev' && $debug === true;
		}

		return $configOption;
	}

}
