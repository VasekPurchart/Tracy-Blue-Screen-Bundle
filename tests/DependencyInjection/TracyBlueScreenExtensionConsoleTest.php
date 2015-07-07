<?php

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use VasekPurchart\TracyBlueScreenBundle\BlueScreen\ConsoleBlueScreenExceptionListener;

class TracyBlueScreenExtensionConsoleTest extends \Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase
{

	public function setUp()
	{
		parent::setUp();
		$this->setParameter('kernel.root_dir', __DIR__);
		$this->setParameter('kernel.logs_dir', __DIR__ . '/tests-logs-dir');
		$this->setParameter('kernel.cache_dir', __DIR__ . '/tests-cache-dir');
		$this->setParameter('kernel.environment', 'dev');
		$this->setParameter('kernel.debug', true);
	}

	/**
	 * @return \Symfony\Component\DependencyInjection\Extension\ExtensionInterface[]
	 */
	protected function getContainerExtensions()
	{
		return [
			new TracyBlueScreenExtension(),
		];
	}

	public function testEnabledByDefault()
	{
		$this->load();

		$this->assertContainerBuilderHasService('vasek_purchart.tracy_blue_screen.blue_screen.console_blue_screen_exception_listener', ConsoleBlueScreenExceptionListener::class);
		$this->assertContainerBuilderHasServiceDefinitionWithTag('vasek_purchart.tracy_blue_screen.blue_screen.console_blue_screen_exception_listener', 'kernel.event_listener', [
			'event' => 'console.exception',
			'priority' => '%' . TracyBlueScreenExtension::CONTAINER_PARAMETER_CONSOLE_LISTENER_PRIORITY . '%',
		]);

		$this->compile();
	}

	public function testDisabled()
	{
		$this->load([
			'console' => [
				'enabled' => false,
			],
		]);

		$this->assertContainerBuilderNotHasService('vasek_purchart.tracy_blue_screen.blue_screen.console_blue_screen_exception_listener');

		$this->compile();
	}

	public function testEnabled()
	{
		$this->load([
			'console' => [
				'enabled' => true,
			],
		]);

		$this->assertContainerBuilderHasService('vasek_purchart.tracy_blue_screen.blue_screen.console_blue_screen_exception_listener', ConsoleBlueScreenExceptionListener::class);
		$this->assertContainerBuilderHasServiceDefinitionWithTag('vasek_purchart.tracy_blue_screen.blue_screen.console_blue_screen_exception_listener', 'kernel.event_listener', [
			'event' => 'console.exception',
			'priority' => '%' . TracyBlueScreenExtension::CONTAINER_PARAMETER_CONSOLE_LISTENER_PRIORITY. '%',
		]);

		$this->compile();
	}

	public function testDefaultLogsDirIsKernelLogsDir()
	{
		$this->load();

		$this->assertContainerBuilderHasParameter(TracyBlueScreenExtension::CONTAINER_PARAMETER_CONSOLE_LOG_DIRECTORY, __DIR__ . '/tests-logs-dir');

		$this->compile();
	}

	public function testCustomLogsDir()
	{
		$this->load([
			'console' => [
				'log_directory' => __DIR__ . '/foobar',
			],
		]);

		$this->assertContainerBuilderHasParameter(TracyBlueScreenExtension::CONTAINER_PARAMETER_CONSOLE_LOG_DIRECTORY, __DIR__ . '/foobar');

		$this->compile();
	}

	public function testDefaultBrowserIsNull()
	{
		$this->load();

		$this->assertContainerBuilderHasParameter(TracyBlueScreenExtension::CONTAINER_PARAMETER_CONSOLE_BROWSER, null);

		$this->compile();
	}

	public function testCustomBrowser()
	{
		$this->load([
			'console' => [
				'browser' => 'google-chrome',
			],
		]);

		$this->assertContainerBuilderHasParameter(TracyBlueScreenExtension::CONTAINER_PARAMETER_CONSOLE_BROWSER, 'google-chrome');

		$this->compile();
	}

	public function testConfigureListenerPriority()
	{
		$this->load([
			'console' => [
				'listener_priority' => 123,
			],
		]);

		$this->assertContainerBuilderHasParameter(TracyBlueScreenExtension::CONTAINER_PARAMETER_CONSOLE_LISTENER_PRIORITY, 123);

		$this->compile();
	}

}
