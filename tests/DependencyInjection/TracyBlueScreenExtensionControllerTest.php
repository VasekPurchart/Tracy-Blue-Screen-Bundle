<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Generator;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use VasekPurchart\TracyBlueScreenBundle\BlueScreen\ControllerBlueScreenExceptionListener;

class TracyBlueScreenExtensionControllerTest extends \Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase
{

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
					'controller' => [
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
		$this->setKernelParameters();
		$this->loadExtensions($configuration);

		$this->assertContainerBuilderHasService('vasek_purchart.tracy_blue_screen.blue_screen.controller_blue_screen_exception_listener', ControllerBlueScreenExceptionListener::class);
		$this->assertContainerBuilderHasServiceDefinitionWithTag('vasek_purchart.tracy_blue_screen.blue_screen.controller_blue_screen_exception_listener', 'kernel.event_listener', [
			'event' => 'kernel.exception',
			'priority' => '%vasek_purchart.tracy_blue_screen.controller.listener_priority%',
		]);
	}

	public function testDisabled(): void
	{
		$this->setKernelParameters();
		$this->loadExtensions([
			'tracy_blue_screen' => [
				'controller' => [
					'enabled' => false,
				],
			],
		]);

		$this->assertContainerBuilderNotHasService('vasek_purchart.tracy_blue_screen.blue_screen.controller_blue_screen_exception_listener');
	}

	public function testConfigureListenerPriority(): void
	{
		$this->setKernelParameters();
		$this->loadExtensions([
			'tracy_blue_screen' => [
				'controller' => [
					'listener_priority' => 123,
				],
			],
		]);

		$this->assertContainerBuilderHasParameter('vasek_purchart.tracy_blue_screen.controller.listener_priority', 123);
	}

	private function setKernelParameters(): void
	{
		$this->setParameter('kernel.root_dir', __DIR__);
		$this->setParameter('kernel.project_dir', __DIR__);
		$this->setParameter('kernel.logs_dir', __DIR__);
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
	 * @param mixed[] $configuration format: extensionAlias(string) => configuration(mixed[])
	 */
	private function loadExtensions(array $configuration = []): void
	{
		TracyBlueScreenExtensionTest::loadExtensionsToContainer($this->container, $configuration, $this->getMinimalConfiguration());
	}

}
