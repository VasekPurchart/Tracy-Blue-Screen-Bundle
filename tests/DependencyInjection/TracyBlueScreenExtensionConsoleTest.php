<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Generator;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use VasekPurchart\TracyBlueScreenBundle\BlueScreen\ConsoleBlueScreenErrorListener;

class TracyBlueScreenExtensionConsoleTest extends \Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase
{

	public function setUp(): void
	{
		parent::setUp();
		$this->setParameter('kernel.root_dir', __DIR__);
		$this->setParameter('kernel.project_dir', __DIR__);
		$this->setParameter('kernel.logs_dir', __DIR__ . '/tests-logs-dir');
		$this->setParameter('kernel.cache_dir', __DIR__ . '/tests-cache-dir');
		$this->setParameter('kernel.environment', 'dev');
		$this->setParameter('kernel.debug', true);
		$this->setParameter('kernel.bundles_metadata', [
			'TwigBundle' => [
				'namespace' => 'Symfony\\Bundle\\TwigBundle',
				'path' => __DIR__,
			],
		]);
	}

	/**
	 * @return \Symfony\Component\DependencyInjection\Extension\ExtensionInterface[]
	 */
	protected function getContainerExtensions(): array
	{
		return [
			new TracyBlueScreenExtension(),
			new TwigExtension(),
		];
	}

	public function enabledDataProvider(): Generator
	{
		yield 'enabled by default' => [
			'configuration' => [],
		];

		yield 'enabled by configuration' => [
			'configuration' => [
				'tracy_blue_screen' => [
					'console' => [
						'enabled' => true,
					],
				],
			],
		];
	}

	/**
	 * @dataProvider enabledDataProvider
	 *
	 * @param mixed[][] $configuration
	 */
	public function testEnabled(array $configuration): void
	{
		$this->loadExtensions($configuration);

		$this->assertContainerBuilderHasService('vasek_purchart.tracy_blue_screen.blue_screen.console_blue_screen_error_listener', ConsoleBlueScreenErrorListener::class);
		$this->assertContainerBuilderHasServiceDefinitionWithTag('vasek_purchart.tracy_blue_screen.blue_screen.console_blue_screen_error_listener', 'kernel.event_listener', [
			'event' => 'console.error',
			'priority' => '%vasek_purchart.tracy_blue_screen.console.listener_priority%',
		]);
	}

	public function testDisabled(): void
	{
		$this->loadExtensions([
			'tracy_blue_screen' => [
				'console' => [
					'enabled' => false,
				],
			],
		]);

		$this->assertContainerBuilderNotHasService('vasek_purchart.tracy_blue_screen.blue_screen.console_blue_screen_error_listener');
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
		$this->loadExtensions($configuration);

		$this->assertContainerBuilderHasParameter($parameterName, $expectedParameterValue);
	}

	/**
	 * @param mixed[] $configuration format: extensionAlias(string) => configuration(mixed[])
	 */
	private function loadExtensions(array $configuration = []): void
	{
		TracyBlueScreenExtensionTest::loadExtensionsToContainer($this->container, $configuration, $this->getMinimalConfiguration());
	}

}
