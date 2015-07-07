<?php

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Tracy\BlueScreen;

class TracyBlueScreenExtensionTest extends \Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase
{

	public function setUp()
	{
		parent::setUp();
		$this->setParameter('kernel.root_dir', __DIR__);
		$this->setParameter('kernel.logs_dir', __DIR__);
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

	public function testOnlyAddCollapsePaths()
	{
		$this->load();

		$this->assertContainerBuilderHasService('vasek_purchart.tracy_blue_screen.tracy.blue_screen.default', BlueScreen::class);

		$blueScreen = $this->container->get('vasek_purchart.tracy_blue_screen.tracy.blue_screen.default');
		$collapsePaths = $blueScreen->collapsePaths;

		$this->assertArrayContainsStringPart('/bootstrap.php.cache', $collapsePaths);
		$this->assertArrayContainsStringPart('/tests-cache-dir', $collapsePaths);
		$this->assertArrayContainsStringPart('/vendor', $collapsePaths);

		$this->compile();
	}

	public function testCollapseCacheDirsByDefault()
	{
		$this->load();

		$this->assertContainerBuilderHasParameter('vasek_purchart.tracy_blue_screen.blue_screen.collapse_paths');
		$collapsePaths = $this->container->getParameter('vasek_purchart.tracy_blue_screen.blue_screen.collapse_paths');

		$this->assertArrayContainsStringPart('/bootstrap.php.cache', $collapsePaths);
		$this->assertArrayContainsStringPart('/tests-cache-dir', $collapsePaths);

		$this->compile();
	}

	public function testSetCollapseDirs()
	{
		$paths = [
			__DIR__ . '/foobar',
		];

		$this->load([
			'blue_screen' => [
				'collapse_paths' => $paths,
			],
		]);

		$this->assertContainerBuilderHasParameter('vasek_purchart.tracy_blue_screen.blue_screen.collapse_paths');
		$collapsePaths = $this->container->getParameter('vasek_purchart.tracy_blue_screen.blue_screen.collapse_paths');

		$this->assertEquals($paths, $collapsePaths);

		$this->compile();
	}

	public function testEmptyCollapseDirs()
	{
		$this->load([
			'blue_screen' => [
				'collapse_paths' => [],
			],
		]);

		$this->assertContainerBuilderHasParameter('vasek_purchart.tracy_blue_screen.blue_screen.collapse_paths');
		$collapsePaths = $this->container->getParameter('vasek_purchart.tracy_blue_screen.blue_screen.collapse_paths');

		$this->assertEmpty($collapsePaths);

		$this->compile();
	}

	/**
	 * @param string $string
	 * @param string[] $array
	 */
	private function assertArrayContainsStringPart($string, array $array)
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
