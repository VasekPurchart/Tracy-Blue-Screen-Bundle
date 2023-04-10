<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Generator;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use VasekPurchart\TracyBlueScreenBundle\BlueScreen\ControllerBlueScreenExceptionListener;

class TracyBlueScreenExtensionControllerTest extends \Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase
{

	public function setUp(): void
	{
		parent::setUp();
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
		$this->loadExtensions($configuration);

		$this->assertContainerBuilderHasService('vasek_purchart.tracy_blue_screen.blue_screen.controller_blue_screen_exception_listener', ControllerBlueScreenExceptionListener::class);
		$this->assertContainerBuilderHasServiceDefinitionWithTag('vasek_purchart.tracy_blue_screen.blue_screen.controller_blue_screen_exception_listener', 'kernel.event_listener', [
			'event' => 'kernel.exception',
			'priority' => '%' . TracyBlueScreenExtension::CONTAINER_PARAMETER_CONTROLLER_LISTENER_PRIORITY . '%',
		]);
	}

	public function testDisabled(): void
	{
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
		$this->loadExtensions([
			'tracy_blue_screen' => [
				'controller' => [
					'listener_priority' => 123,
				],
			],
		]);

		$this->assertContainerBuilderHasParameter(TracyBlueScreenExtension::CONTAINER_PARAMETER_CONTROLLER_LISTENER_PRIORITY, 123);
	}

	/**
	 * @param mixed[] $configuration format: extensionAlias(string) => configuration(mixed[])
	 */
	private function loadExtensions(array $configuration = []): void
	{
		TracyBlueScreenExtensionTest::loadExtensionsToContainer($this->container, $configuration, $this->getMinimalConfiguration());
	}

}
