<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Generator;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Controller\ErrorController;

class ReplaceErrorControllerErrorRendererCompilerPassTest extends \PHPUnit\Framework\TestCase
{

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
	 * @param mixed[]|null|array $tracyBlueScreenConfiguration
	 * @param bool $expectToBeReplaced
	 */
	public function testReplaceErrorRenderer(
		string $kernelEnvironment,
		bool $kernelDebugParameter,
		?array $tracyBlueScreenConfiguration,
		bool $expectToBeReplaced
	): void
	{
		$container = TracyBlueScreenExtensionTest::createContainer();
		$container->registerExtension(new FrameworkExtension());
		$container->registerExtension(new TracyBlueScreenExtension());
		$container->addCompilerPass(new ReplaceErrorControllerErrorRendererCompilerPass());

		$container->setParameter('kernel.project_dir', __DIR__);
		$container->setParameter('kernel.logs_dir', __DIR__ . '/tests-logs-dir');
		$container->setParameter('kernel.build_dir', __DIR__ . '/tests-build-dir');
		$container->setParameter('kernel.cache_dir', __DIR__ . '/tests-cache-dir');
		$container->setParameter('kernel.environment', $kernelEnvironment);
		$container->setParameter('kernel.debug', $kernelDebugParameter);
		$container->setParameter('kernel.container_class', __CLASS__);

		$container->loadFromExtension('framework');
		$container->loadFromExtension('tracy_blue_screen', $tracyBlueScreenConfiguration);

		$container->compile();

		$serviceId = 'error_controller';
		TracyBlueScreenExtensionTest::assertContainerHasService($container, $serviceId);
		TracyBlueScreenExtensionTest::assertContainerServiceIsOfType($container, $serviceId, ErrorController::class);

		$serviceDefinition = $container->findDefinition($serviceId);

		if ($expectToBeReplaced) {
			$argument = $serviceDefinition->getArgument('$errorRenderer');
			Assert::assertInstanceOf(Reference::class, $argument);
			Assert::assertSame('vasek_purchart.tracy_blue_screen.blue_screen.error_renderer', $argument->__toString());
		} else {
			$argument = $serviceDefinition->getArgument(2);
			Assert::assertInstanceOf(Reference::class, $argument);
			Assert::assertSame('error_renderer', $argument->__toString());
		}
	}

	public function testCustomErrorControllerWithControllerEnabled(): void
	{
		$container = TracyBlueScreenExtensionTest::createContainer();
		$container->registerExtension(new FrameworkExtension());
		$container->registerExtension(new TracyBlueScreenExtension());
		$container->addCompilerPass(new ReplaceErrorControllerErrorRendererCompilerPass());

		$container->setParameter('kernel.root_dir', __DIR__);
		$container->setParameter('kernel.project_dir', __DIR__);
		$container->setParameter('kernel.logs_dir', __DIR__ . '/tests-logs-dir');
		$container->setParameter('kernel.cache_dir', __DIR__ . '/tests-cache-dir');
		$container->setParameter('kernel.environment', 'dev');
		$container->setParameter('kernel.debug', true);
		$container->setParameter('kernel.container_class', __CLASS__);

		$container->loadFromExtension('framework');
		$container->loadFromExtension('tracy_blue_screen');

		$customErrorControllerClass = 'FooBar';
		$container->setDefinition('error_controller', new Definition($customErrorControllerClass));

		try {
			$container->compile();

			Assert::fail('Exception expected');

		} catch (\VasekPurchart\TracyBlueScreenBundle\DependencyInjection\CannotReplaceErrorRendererForNonDefaultErrorControllerException $e) {
			Assert::assertSame($customErrorControllerClass, $e->getCustomErrorControllerClass());
		}
	}

}
