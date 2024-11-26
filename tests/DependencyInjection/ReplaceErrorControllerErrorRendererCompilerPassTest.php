<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Generator;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Controller\ErrorController;

class ReplaceErrorControllerErrorRendererCompilerPassTest extends \Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase
{

	protected function registerCompilerPass(ContainerBuilder $container): void
	{
		$this->container->registerExtension(new FrameworkExtension());

		$this->container->registerExtension(new TracyBlueScreenExtension());
		$container->addCompilerPass(new ReplaceErrorControllerErrorRendererCompilerPass());
	}

	public function replaceErrorRendererDataProvider(): Generator
	{
		yield 'debug: true, dev env, default configuration' => [
			'kernelEnvironment' => 'dev',
			'kernelDebugParameter' => true,
			'tracyBlueScreenConfiguration' => null,
			'expectToBeReplaced' => true,
		];

		yield 'debug: false, dev env, default configuration' => [
			'kernelEnvironment' => 'dev',
			'kernelDebugParameter' => false,
			'tracyBlueScreenConfiguration' => null,
			'expectToBeReplaced' => false,
		];

		yield 'debug: true, prod env, default configuration' => [
			'kernelEnvironment' => 'prod',
			'kernelDebugParameter' => true,
			'tracyBlueScreenConfiguration' => null,
			'expectToBeReplaced' => false,
		];

		yield 'debug: true, "unknown" env, default configuration' => [
			'kernelEnvironment' => 'xxx',
			'kernelDebugParameter' => true,
			'tracyBlueScreenConfiguration' => null,
			'expectToBeReplaced' => false,
		];

		yield 'debug: true, dev env, controller explicitly enabled' => [
			'kernelEnvironment' => 'dev',
			'kernelDebugParameter' => true,
			'tracyBlueScreenConfiguration' => [
				'controller' => [
					'enabled' => true,
				],
			],
			'expectToBeReplaced' => true,
		];

		yield 'debug: false, dev env, controller explicitly enabled' => [
			'kernelEnvironment' => 'dev',
			'kernelDebugParameter' => false,
			'tracyBlueScreenConfiguration' => [
				'controller' => [
					'enabled' => true,
				],
			],
			'expectToBeReplaced' => true,
		];

		yield 'debug: true, prod env, controller explicitly enabled' => [
			'kernelEnvironment' => 'prod',
			'kernelDebugParameter' => true,
			'tracyBlueScreenConfiguration' => [
				'controller' => [
					'enabled' => true,
				],
			],
			'expectToBeReplaced' => true,
		];

		yield 'debug: true, dev env, controller explicitly disabled' => [
			'kernelEnvironment' => 'dev',
			'kernelDebugParameter' => true,
			'tracyBlueScreenConfiguration' => [
				'controller' => [
					'enabled' => false,
				],
			],
			'expectToBeReplaced' => false,
		];
	}

	/**
	 * @dataProvider replaceErrorRendererDataProvider
	 *
	 * @param string $kernelEnvironment
	 * @param bool $kernelDebugParameter
	 * @param mixed[]|null $tracyBlueScreenConfiguration
	 * @param bool $expectToBeReplaced
	 */
	public function testReplaceErrorRenderer(
		string $kernelEnvironment,
		bool $kernelDebugParameter,
		?array $tracyBlueScreenConfiguration,
		bool $expectToBeReplaced
	): void
	{
		$this->setParameter('kernel.root_dir', __DIR__);
		$this->setParameter('kernel.project_dir', __DIR__);
		$this->setParameter('kernel.logs_dir', __DIR__ . '/tests-logs-dir');
		$this->setParameter('kernel.cache_dir', __DIR__ . '/tests-cache-dir');
		$this->setParameter('kernel.environment', $kernelEnvironment);
		$this->setParameter('kernel.debug', $kernelDebugParameter);
		$this->setParameter('kernel.container_class', __CLASS__);

		$this->container->loadFromExtension('framework');
		$this->container->loadFromExtension('tracy_blue_screen', $tracyBlueScreenConfiguration);

		$this->compile();

		$this->assertContainerBuilderHasService('error_controller', ErrorController::class);

		if ($expectToBeReplaced) {
			$this->assertContainerBuilderHasServiceDefinitionWithArgument(
				'error_controller',
				'$errorRenderer',
				new Reference('vasek_purchart.tracy_blue_screen.blue_screen.error_renderer')
			);
		} else {
			$this->assertContainerBuilderHasServiceDefinitionWithArgument(
				'error_controller',
				2,
				new Reference('error_renderer')
			);
		}
	}

	public function testCustomErrorControllerWithControllerEnabled(): void
	{
		$this->setParameter('kernel.root_dir', __DIR__);
		$this->setParameter('kernel.project_dir', __DIR__);
		$this->setParameter('kernel.logs_dir', __DIR__ . '/tests-logs-dir');
		$this->setParameter('kernel.cache_dir', __DIR__ . '/tests-cache-dir');
		$this->setParameter('kernel.environment', 'dev');
		$this->setParameter('kernel.debug', true);
		$this->setParameter('kernel.container_class', __CLASS__);

		$this->container->loadFromExtension('framework');
		$this->container->loadFromExtension('tracy_blue_screen');

		$customErrorControllerClass = 'FooBar';
		$this->container->setDefinition('error_controller', new Definition($customErrorControllerClass));

		try {
			$this->compile();

			Assert::fail('Exception expected');

		} catch (\VasekPurchart\TracyBlueScreenBundle\DependencyInjection\CannotReplaceErrorRendererForNonDefaultErrorControllerException $e) {
			Assert::assertSame($customErrorControllerClass, $e->getCustomErrorControllerClass());
		}
	}

}
