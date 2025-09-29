<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Generator;
use PHPUnit\Framework\Assert;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VasekPurchart\TracyBlueScreenBundle\BlueScreen\ConsoleBlueScreenErrorListener;

class TracyBlueScreenExtensionConsoleTest extends \PHPUnit\Framework\TestCase
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

		yield 'debug: true, dev env, console explicitly enabled' => [
			'kernelEnvironment' => 'dev',
			'kernelDebugParameter' => true,
			'configuration' => [
				'tracy_blue_screen' => [
					'console' => [
						'enabled' => true,
					],
				],
			],
			'expectToBeEnabled' => true,
		];

		yield 'debug: false, dev env, console explicitly enabled' => [
			'kernelEnvironment' => 'dev',
			'kernelDebugParameter' => false,
			'configuration' => [
				'tracy_blue_screen' => [
					'console' => [
						'enabled' => true,
					],
				],
			],
			'expectToBeEnabled' => true,
		];

		yield 'debug: true, prod env, console explicitly enabled' => [
			'kernelEnvironment' => 'prod',
			'kernelDebugParameter' => true,
			'configuration' => [
				'tracy_blue_screen' => [
					'console' => [
						'enabled' => true,
					],
				],
			],
			'expectToBeEnabled' => true,
		];

		yield 'debug: true, dev env, console explicitly disabled' => [
			'kernelEnvironment' => 'dev',
			'kernelDebugParameter' => true,
			'configuration' => [
				'tracy_blue_screen' => [
					'console' => [
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

		$serviceId = 'vasek_purchart.tracy_blue_screen.blue_screen.console_blue_screen_error_listener';

		if ($expectToBeEnabled) {
			TracyBlueScreenExtensionTest::assertContainerHasService($container, $serviceId);
			TracyBlueScreenExtensionTest::assertContainerServiceIsOfType($container, $serviceId, ConsoleBlueScreenErrorListener::class);
			TracyBlueScreenExtensionTest::assertContainerServiceHasTagWithAttributes(
				$container,
				$serviceId,
				'kernel.event_listener',
				[
					'event' => 'console.error',
					'priority' => '%vasek_purchart.tracy_blue_screen.console.listener_priority%',
				]
			);
		} else {
			TracyBlueScreenExtensionTest::assertContainerDoesNotHaveService($container, $serviceId);
		}
	}

	/**
	 * @return mixed[][]|\Generator
	 */
	public function configureContainerParameterDataProvider(): Generator
	{
		yield 'default logs dir is kernel logs dir' => [
			'configuration' => [],
			'parameterName' => 'vasek_purchart.tracy_blue_screen.console.log_directory',
			'expectedParameterValue' => __DIR__ . '/tests-logs-dir',
		];

		yield 'custom logs dir' => [
			'configuration' => [
				'tracy_blue_screen' => [
					'console' => [
						'log_directory' => __DIR__ . '/foobar',
					],
				],
			],
			'parameterName' => 'vasek_purchart.tracy_blue_screen.console.log_directory',
			'expectedParameterValue' => __DIR__ . '/foobar',
		];

		yield 'default browser is null' => [
			'configuration' => [],
			'parameterName' => 'vasek_purchart.tracy_blue_screen.console.browser',
			'expectedParameterValue' => null,
		];

		yield 'custom browser' => [
			'configuration' => [
				'tracy_blue_screen' => [
					'console' => [
						'browser' => 'google-chrome',
					],
				],
			],
			'parameterName' => 'vasek_purchart.tracy_blue_screen.console.browser',
			'expectedParameterValue' => 'google-chrome',
		];

		yield 'listener priority' => [
			'configuration' => [
				'tracy_blue_screen' => [
					'console' => [
						'listener_priority' => 123,
					],
				],
			],
			'parameterName' => 'vasek_purchart.tracy_blue_screen.console.listener_priority',
			'expectedParameterValue' => 123,
		];
	}

	/**
	 * @dataProvider configureContainerParameterDataProvider
	 *
	 * @param mixed[][]|array $configuration
	 * @param string $parameterName
	 * @param mixed $expectedParameterValue
	 */
	public function testConfigureContainerParameter(
		array $configuration,
		string $parameterName,
		$expectedParameterValue
	): void
	{
		$container = TracyBlueScreenExtensionTest::createContainer();
		$this->setKernelParameters($container, 'dev', true);

		$container->registerExtension(new TracyBlueScreenExtension());
		TracyBlueScreenExtensionTest::loadRegisteredExtensionsUsingConfigurationsByAlias($container, $configuration);

		TracyBlueScreenExtensionTest::assertContainerHasParameter($container, $parameterName);
		Assert::assertSame($expectedParameterValue, $container->getParameter($parameterName));
	}

	private function setKernelParameters(
		ContainerBuilder $container,
		string $kernelEnvironment,
		bool $kernelDebugParameter
	): void
	{
		$container->setParameter('kernel.project_dir', __DIR__);
		$container->setParameter('kernel.logs_dir', __DIR__ . '/tests-logs-dir');
		$container->setParameter('kernel.cache_dir', __DIR__ . '/tests-cache-dir');
		$container->setParameter('kernel.environment', $kernelEnvironment);
		$container->setParameter('kernel.debug', $kernelDebugParameter);
	}

}
