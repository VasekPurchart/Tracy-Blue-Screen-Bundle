<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Tracy\BlueScreen;

class TracyBlueScreenExtensionTest extends \Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase
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

	public function testDependsOnTwigBundle(): void
	{
		$containerBuilder = new ContainerBuilder();
		$extension = new TracyBlueScreenExtension();

		$this->expectException(\VasekPurchart\TracyBlueScreenBundle\DependencyInjection\TwigBundleRequiredException::class);
		$extension->prepend($containerBuilder);
	}

	public function testOnlyAddCollapsePaths(): void
	{
		$this->loadExtensions();

		$this->assertContainerBuilderHasService('vasek_purchart.tracy_blue_screen.tracy.blue_screen.default', BlueScreen::class);

		$blueScreen = $this->container->get('vasek_purchart.tracy_blue_screen.tracy.blue_screen.default');
		$collapsePaths = $blueScreen->collapsePaths;

		$this->assertArrayContainsStringPart('/bootstrap.php.cache', $collapsePaths);
		$this->assertArrayContainsStringPart('/tests-cache-dir', $collapsePaths);
		$this->assertArrayContainsStringPart('/vendor', $collapsePaths);
	}

	public function testCollapseCacheDirsByDefault(): void
	{
		$this->loadExtensions();

		$this->assertContainerBuilderHasParameter('vasek_purchart.tracy_blue_screen.blue_screen.collapse_paths');
		$collapsePaths = $this->container->getParameter('vasek_purchart.tracy_blue_screen.blue_screen.collapse_paths');

		$this->assertArrayContainsStringPart('/bootstrap.php.cache', $collapsePaths);
		$this->assertArrayContainsStringPart('/tests-cache-dir', $collapsePaths);
	}

	public function testSetCollapseDirs(): void
	{
		$paths = [
			__DIR__ . '/foobar',
		];

		$this->loadExtensions([
			'tracy_blue_screen' => [
				'blue_screen' => [
					'collapse_paths' => $paths,
				],
			],
		]);

		$this->assertContainerBuilderHasParameter('vasek_purchart.tracy_blue_screen.blue_screen.collapse_paths');
		$collapsePaths = $this->container->getParameter('vasek_purchart.tracy_blue_screen.blue_screen.collapse_paths');

		$this->assertEquals($paths, $collapsePaths);
	}

	public function testEmptyCollapseDirs(): void
	{
		$this->loadExtensions([
			'tracy_blue_screen' => [
				'blue_screen' => [
					'collapse_paths' => [],
				],
			],
		]);

		$this->assertContainerBuilderHasParameter('vasek_purchart.tracy_blue_screen.blue_screen.collapse_paths');
		$collapsePaths = $this->container->getParameter('vasek_purchart.tracy_blue_screen.blue_screen.collapse_paths');

		$this->assertEmpty($collapsePaths);
	}

	/**
	 * @param mixed[] $configuration format: extensionAlias(string) => configuration(mixed[])
	 */
	private function loadExtensions(array $configuration = []): void
	{
		self::loadExtensionsToContainer($this->container, $configuration, $this->getMinimalConfiguration());
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 * @param mixed[] $configuration format: extensionAlias(string) => configuration(mixed[])
	 * @param mixed[] $minimalConfiguration format: extensionAlias(string) => configuration(mixed[])
	 */
	public static function loadExtensionsToContainer(
		ContainerBuilder $container,
		array $configuration = [],
		array $minimalConfiguration = []
	): void
	{
		$configurations = [];
		foreach ($container->getExtensions() as $extensionAlias => $extension) {
			$configurations[$extensionAlias] = [];
			if (array_key_exists($extensionAlias, $minimalConfiguration)) {
				$container->loadFromExtension($extensionAlias, $minimalConfiguration[$extensionAlias]);
				$configurations[$extensionAlias][] = $minimalConfiguration[$extensionAlias];
			}
			if (array_key_exists($extensionAlias, $configuration)) {
				$container->loadFromExtension($extensionAlias, $configuration[$extensionAlias]);
				$configurations[$extensionAlias][] = $configuration[$extensionAlias];
			}
		}
		foreach ($container->getExtensions() as $extensionAlias => $extension) {
			if ($extension instanceof PrependExtensionInterface) {
				$extension->prepend($container);
			}
		}
		foreach ($container->getExtensions() as $extensionAlias => $extension) {
			$extension->load($configurations[$extensionAlias], $container);
		}
	}

	/**
	 * @param string $string
	 * @param string[] $array
	 */
	private function assertArrayContainsStringPart(string $string, array $array): void
	{
		$found = false;
		foreach ($array as $item) {
			if (strpos($item, $string) !== false) {
				$found = true;
				break;
			}
		}
		$this->assertTrue($found, sprintf('%s not found in any elements of the given %s', $string, var_export($array, true)));
	}

}
