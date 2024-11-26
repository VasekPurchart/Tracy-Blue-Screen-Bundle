<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Generator;
use VasekPurchart\TracyBlueScreenBundle\BlueScreen\BlueScreenErrorRenderer;

class TracyBlueScreenExtensionControllerTest extends \Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase
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

		$this->assertContainerBuilderHasParameter('vasek_purchart.tracy_blue_screen.controller.enabled', $expectToBeEnabled);
		// should be present even if disabled, so that it can be used in custom error controller if needed
		$this->assertContainerBuilderHasService('vasek_purchart.tracy_blue_screen.blue_screen.error_renderer', BlueScreenErrorRenderer::class);
	}

	private function setKernelParameters(
		string $kernelEnvironment,
		bool $kernelDebugParameter
	): void
	{
		$this->setParameter('kernel.root_dir', __DIR__);
		$this->setParameter('kernel.project_dir', __DIR__);
		$this->setParameter('kernel.logs_dir', __DIR__);
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
