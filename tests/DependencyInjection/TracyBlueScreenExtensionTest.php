<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Generator;
use PHPUnit\Framework\Assert;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Tracy\BlueScreen;

class TracyBlueScreenExtensionTest extends \Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase
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

	public function testOnlyAddCollapsePaths(): void
	{
		$this->setKernelParameters();
		$this->loadExtensions();

		$this->assertContainerBuilderHasService('vasek_purchart.tracy_blue_screen.tracy.blue_screen.default', BlueScreen::class);

		$blueScreen = $this->container->get('vasek_purchart.tracy_blue_screen.tracy.blue_screen.default');
		$collapsePaths = $blueScreen->collapsePaths;

		$this->assertArrayContainsStringPart('/bootstrap.php.cache', $collapsePaths);
		$this->assertArrayContainsStringPart('/tests-cache-dir', $collapsePaths);
		$this->assertArrayContainsStringPart('/vendor', $collapsePaths);
	}

	/**
	 * @return mixed[][]|\Generator
	 */
	public function collapsePathsConfigurationDataProvider(): Generator
	{
		yield 'collapse cache dirs by default' => [
			'configuration' => [],
			'expectedCollapsePaths' => [
				'/bootstrap.php.cache',
				'/tests-cache-dir',
			],
		];

		yield 'set collapse dirs' => (static function (): array {
			$paths = [
				__DIR__ . '/foobar',
			];

			return [
				'configuration' => [
					'tracy_blue_screen' => [
						'blue_screen' => [
							'collapse_paths' => $paths,
						],
					],
				],
				'expectedCollapsePaths' => $paths,
			];
		})();

		yield 'empty collapse dirs' => (static function (): array {
			return [
				'configuration' => [
					'tracy_blue_screen' => [
						'blue_screen' => [
							'collapse_paths' => [],
						],
					],
				],
				'expectedCollapsePaths' => [],
			];
		})();
	}

	/**
	 * @dataProvider collapsePathsConfigurationDataProvider
	 *
	 * @param mixed[][] $configuration
	 * @param string[] $expectedCollapsePaths
	 */
	public function testCollapsePathsConfiguration(
		array $configuration,
		array $expectedCollapsePaths
	): void
	{
		$this->setKernelParameters();
		$this->loadExtensions($configuration);

		$this->assertContainerBuilderHasParameter('vasek_purchart.tracy_blue_screen.blue_screen.collapse_paths');
		$collapsePaths = $this->container->getParameter('vasek_purchart.tracy_blue_screen.blue_screen.collapse_paths');

		foreach ($expectedCollapsePaths as $expectedCollapsePath) {
			$this->assertArrayContainsStringPart($expectedCollapsePath, $collapsePaths);
		}
		Assert::assertCount(count($expectedCollapsePaths), $collapsePaths);
	}

	private function setKernelParameters(): void
	{
		$this->setParameter('kernel.project_dir', __DIR__);
		$this->setParameter('kernel.logs_dir', __DIR__);
		$this->setParameter('kernel.cache_dir', __DIR__ . '/tests-cache-dir');
		$this->setParameter('kernel.environment', 'dev');
		$this->setParameter('kernel.debug', true);
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
		Assert::assertTrue($found, sprintf('%s not found in any elements of the given %s', $string, var_export($array, true)));
	}

}
