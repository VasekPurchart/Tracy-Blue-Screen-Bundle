<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Controller\ErrorController;

class ReplaceErrorControllerErrorRendererCompilerPass implements \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{

	public function process(ContainerBuilder $container): void
	{
		if (
			!$container->hasParameter('vasek_purchart.tracy_blue_screen.controller.enabled')
			|| !$container->getParameter('vasek_purchart.tracy_blue_screen.controller.enabled')
		) {
			return;
		}

		$errorControllerDefinition = $container->findDefinition('error_controller');
		if ($errorControllerDefinition->getClass() !== ErrorController::class) {
			throw new \VasekPurchart\TracyBlueScreenBundle\DependencyInjection\CannotReplaceErrorRendererForNonDefaultErrorControllerException($errorControllerDefinition->getClass());
		}

		$errorControllerDefinition->setArgument(
			'$errorRenderer',
			new Reference('vasek_purchart.tracy_blue_screen.blue_screen.error_renderer')
		);
	}

}
