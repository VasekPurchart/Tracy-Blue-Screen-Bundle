<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Generator;
use PHPUnit\Framework\Assert;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VasekPurchart\TracyBlueScreenBundle\BlueScreen\BlueScreenErrorRenderer;

class TracyBlueScreenExtensionControllerTest extends \PHPUnit\Framework\TestCase
{

	public function enabledDataProvider(): Generator
	{
		yield 'debug: true, dev env, default configuration' => [
			'kernelEnvironment' => 'dev',
			'kernelDebugParameter' => true,
			'configuration' => [],
			'expectToBeEnabled' => true,
		];

		yield 'debug: false, dev env, default configuration' => [
			'kernelEnvironment' => 'dev',
			'kernelDebugParameter' => false,
			'configuration' => [],
			'expectToBeEnabled' => false,
		];

		yield 'debug: true, prod env, default configuration' => [
			'kernelEnvironment' => 'prod',
			'kernelDebugParameter' => true,
			'configuration' => [],
			'expectToBeEnabled' => false,
		];

		yield 'debug: true, "unknown" env, default configuration' => [
			'kernelEnvironment' => 'xxx',
			'kernelDebugParameter' => true,
			'configuration' => [],
			'expectToBeEnabled' => false,
		];

		yield 'debug: true, dev env, controller explicitly enabled' => [
			'kernelEnvironment' => 'dev',
			'kernelDebugParameter' => true,
			'configuration' => [
				'tracy_blue_screen' => [
					'controller' => [
						'enabled' => true,
					],
				],
			],
			'expectToBeEnabled' => true,
		];

		yield 'debug: false, dev env, controller explicitly enabled' => [
			'kernelEnvironment' => 'dev',
			'kernelDebugParameter' => false,
			'configuration' => [
				'tracy_blue_screen' => [
					'controller' => [
						'enabled' => true,
					],
				],
			],
			'expectToBeEnabled' => true,
		];

		yield 'debug: true, prod env, controller explicitly enabled' => [
			'kernelEnvironment' => 'prod',
			'kernelDebugParameter' => true,
			'configuration' => [
				'tracy_blue_screen' => [
					'controller' => [
						'enabled' => true,
					],
				],
			],
			'expectToBeEnabled' => true,
		];

		yield 'debug: true, dev env, controller explicitly disabled' => [
			'kernelEnvironment' => 'dev',
			'kernelDebugParameter' => true,
			'configuration' => [
				'tracy_blue_screen' => [
					'controller' => [
						'enabled' => false,
					],
				],
			],
			'expectToBeEnabled' => false,
		];
	}

	/**
	 * @dataProvider enabledDataProvider
	 *
	 * @param string $kernelEnvironment
	 * @param bool $kernelDebugParameter
	 * @param mixed[][]|array $configuration
	 * @param bool $expectToBeEnabled
	 */
	public function testEnabled(
		string $kernelEnvironment,
		bool $kernelDebugParameter,
		array $configuration,
		bool $expectToBeEnabled
	): void
	{
		$container = TracyBlueScreenExtensionTest::createContainer();
		$this->setKernelParameters($container, $kernelEnvironment, $kernelDebugParameter);

		$container->registerExtension(new TracyBlueScreenExtension());
		TracyBlueScreenExtensionTest::loadRegisteredExtensionsUsingConfigurationsByAlias($container, $configuration);

		TracyBlueScreenExtensionTest::assertContainerHasParameter($container, 'vasek_purchart.tracy_blue_screen.controller.enabled');
		Assert::assertSame($expectToBeEnabled, $container->getParameter('vasek_purchart.tracy_blue_screen.controller.enabled'));

		// should be present even if disabled, so that it can be used in custom error controller if needed
		$serviceId = 'vasek_purchart.tracy_blue_screen.blue_screen.error_renderer';
		TracyBlueScreenExtensionTest::assertContainerHasService($container, $serviceId);
		TracyBlueScreenExtensionTest::assertContainerServiceIsOfType($container, $serviceId, BlueScreenErrorRenderer::class);
	}

	private function setKernelParameters(
		ContainerBuilder $container,
		string $kernelEnvironment,
		bool $kernelDebugParameter
	): void
	{
		$container->setParameter('kernel.project_dir', __DIR__);
		$container->setParameter('kernel.logs_dir', __DIR__);
		$container->setParameter('kernel.cache_dir', __DIR__ . '/tests-cache-dir');
		$container->setParameter('kernel.environment', $kernelEnvironment);
		$container->setParameter('kernel.debug', $kernelDebugParameter);
	}

}
