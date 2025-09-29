<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Generator;
use PHPUnit\Framework\Assert;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Tracy\BlueScreen;

class TracyBlueScreenExtensionTest extends \PHPUnit\Framework\TestCase
{

	public function testOnlyAddCollapsePaths(): void
	{
		$container = self::createContainer();
		$this->setKernelParameters($container);

		$container->registerExtension(new TracyBlueScreenExtension());
		self::loadRegisteredExtensionsUsingConfigurationsByAlias($container);

		$serviceId = 'vasek_purchart.tracy_blue_screen.tracy.blue_screen.default';

		self::assertContainerHasService($container, $serviceId);
		self::assertContainerServiceIsOfType($container, $serviceId, BlueScreen::class);

		$blueScreen = $container->get($serviceId);
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
	 * @param mixed[][]|array $configuration
	 * @param string[]|array $expectedCollapsePaths
	 */
	public function testCollapsePathsConfiguration(
		array $configuration,
		array $expectedCollapsePaths
	): void
	{
		$container = self::createContainer();
		$this->setKernelParameters($container);

		$container->registerExtension(new TracyBlueScreenExtension());
		self::loadRegisteredExtensionsUsingConfigurationsByAlias($container, $configuration);

		self::assertContainerHasParameter($container, 'vasek_purchart.tracy_blue_screen.blue_screen.collapse_paths');
		$collapsePaths = $container->getParameter('vasek_purchart.tracy_blue_screen.blue_screen.collapse_paths');

		foreach ($expectedCollapsePaths as $expectedCollapsePath) {
			$this->assertArrayContainsStringPart($expectedCollapsePath, $collapsePaths);
		}
		Assert::assertCount(count($expectedCollapsePaths), $collapsePaths);
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 * @param mixed[][]|array $configurationsByAlias format: extensionAlias(string) => configuration(mixed[])
	 */
	public static function loadRegisteredExtensionsUsingConfigurationsByAlias(
		ContainerBuilder $container,
		array $configurationsByAlias = []
	): void
	{
		foreach ($container->getExtensions() as $extension) {
			if ($extension instanceof PrependExtensionInterface) {
				$extension->prepend($container);
			}
		}

		foreach ($container->getExtensions() as $extensionAlias => $extension) {
			if (array_key_exists($extensionAlias, $configurationsByAlias)) {
				$extension->load([$configurationsByAlias[$extensionAlias]], $container);
			} else {
				$extension->load([], $container);
			}
		}
	}

	public static function createContainer(): ContainerBuilder
	{
		$container = new ContainerBuilder(new ParameterBag([]));
		$container->getCompilerPassConfig()->setOptimizationPasses([]);
		$container->getCompilerPassConfig()->setRemovingPasses([]);
		$container->getCompilerPassConfig()->setAfterRemovingPasses([]);

		return $container;
	}
	public static function assertContainerHasService(
		ContainerBuilder $container,
		string $serviceId
	): void
	{
		Assert::assertTrue(
			$container->has($serviceId),
			sprintf('Expecting the container to have service `%s`.', $serviceId)
		);
	}

	public static function assertContainerDoesNotHaveService(
		ContainerBuilder $container,
		string $serviceId
	): void
	{
		Assert::assertFalse(
			$container->has($serviceId),
			sprintf('Expecting the container to not have service `%s`.', $serviceId)
		);
	}

	public static function assertContainerServiceIsOfType(
		ContainerBuilder $container,
		string $serviceId,
		string $expectedClassString
	): void
	{
		$serviceDefinition = $container->findDefinition($serviceId);

		Assert::assertSame(
			$expectedClassString,
			$container->getParameterBag()->resolveValue($serviceDefinition->getClass()),
			sprintf('Expecting the service `%s` to be of type `%s`.', $serviceId, $expectedClassString)
		);
	}

	public static function assertContainerServiceHasTag(
		ContainerBuilder $container,
		string $serviceId,
		string $tagName
	): void
	{
		$serviceDefinition = $container->findDefinition($serviceId);

		Assert::assertTrue(
			$serviceDefinition->hasTag($tagName),
			sprintf('Expecting the service `%s` to have tag `%s`.', $serviceId, $tagName)
		);
	}

	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 * @param string $serviceId
	 * @param string $tagName
	 * @param mixed[]|array $expectedTagAttributes
	 */
	public static function assertContainerServiceHasTagWithAttributes(
		ContainerBuilder $container,
		string $serviceId,
		string $tagName,
		array $expectedTagAttributes
	): void
	{
		self::assertContainerServiceHasTag($container, $serviceId, $tagName);

		$serviceDefinition = $container->findDefinition($serviceId);
		$allTagAttributes = $serviceDefinition->getTag($tagName);

		if (count($allTagAttributes) === 0) {
			Assert::fail(sprintf('Expecting the tag `%s` to have attributes.', $tagName));
		}

		foreach ($allTagAttributes as $tagAttributes) {
			Assert::assertSame(
				$expectedTagAttributes,
				$tagAttributes,
				sprintf('Expecting the tag `%s` to have expected attributes.', $serviceId)
			);
		}
	}

	public static function assertContainerHasParameter(
		ContainerBuilder $container,
		string $parameterName
	): void
	{
		Assert::assertTrue(
			$container->hasParameter($parameterName),
			sprintf('Expecting the container to have parameter `%s`.', $parameterName)
		);
	}

	private function setKernelParameters(ContainerBuilder $container): void
	{
		$container->setParameter('kernel.project_dir', __DIR__);
		$container->setParameter('kernel.logs_dir', __DIR__);
		$container->setParameter('kernel.cache_dir', __DIR__ . '/tests-cache-dir');
		$container->setParameter('kernel.environment', 'dev');
		$container->setParameter('kernel.debug', true);
	}

	/**
	 * @param string $string
	 * @param string[]|array $array
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
