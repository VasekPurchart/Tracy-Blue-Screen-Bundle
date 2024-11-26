<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Generator;
use VasekPurchart\TracyBlueScreenBundle\BlueScreen\ConsoleBlueScreenErrorListener;

class TracyBlueScreenExtensionConsoleTest extends \Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase
{

	/**
	 * @return \Symfony\Component\DependencyInjection\Extension\ExtensionInterface[]
	 */
	protected function getContainerExtensions(): array
	{
		return [
			new TracyBlueScreenExtension(),
		];
	}

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
	 * @param mixed[][] $configuration
	 * @param bool $expectToBeEnabled
	 */
	public function testEnabled(
		string $kernelEnvironment,
		bool $kernelDebugParameter,
		array $configuration,
		bool $expectToBeEnabled
	): void
	{
		$this->setKernelParameters($kernelEnvironment, $kernelDebugParameter);
		$this->loadExtensions($configuration);

		if ($expectToBeEnabled) {
			$this->assertContainerBuilderHasService('vasek_purchart.tracy_blue_screen.blue_screen.console_blue_screen_error_listener', ConsoleBlueScreenErrorListener::class);
			$this->assertContainerBuilderHasServiceDefinitionWithTag('vasek_purchart.tracy_blue_screen.blue_screen.console_blue_screen_error_listener', 'kernel.event_listener', [
				'event' => 'console.error',
				'priority' => '%vasek_purchart.tracy_blue_screen.console.listener_priority%',
			]);
		} else {
			$this->assertContainerBuilderNotHasService('vasek_purchart.tracy_blue_screen.blue_screen.console_blue_screen_error_listener');
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
	 * @param mixed[][] $configuration
	 * @param string $parameterName
	 * @param mixed $expectedParameterValue
	 */
	public function testConfigureContainerParameter(
		array $configuration,
		string $parameterName,
		$expectedParameterValue
	): void
	{
		$this->setKernelParameters('dev', true);
		$this->loadExtensions($configuration);

		$this->assertContainerBuilderHasParameter($parameterName, $expectedParameterValue);
	}

	private function setKernelParameters(
		string $kernelEnvironment,
		bool $kernelDebugParameter
	): void
	{
		$this->setParameter('kernel.root_dir', __DIR__);
		$this->setParameter('kernel.project_dir', __DIR__);
		$this->setParameter('kernel.logs_dir', __DIR__ . '/tests-logs-dir');
		$this->setParameter('kernel.cache_dir', __DIR__ . '/tests-cache-dir');
		$this->setParameter('kernel.environment', $kernelEnvironment);
		$this->setParameter('kernel.debug', $kernelDebugParameter);
	}

	/**
	 * @param mixed[] $configuration format: extensionAlias(string) => configuration(mixed[])
	 */
	private function loadExtensions(array $configuration = []): void
	{
		TracyBlueScreenExtensionTest::loadExtensionsToContainer($this->container, $configuration, $this->getMinimalConfiguration());
	}

}
