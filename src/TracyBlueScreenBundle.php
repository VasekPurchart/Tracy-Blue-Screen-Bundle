<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use VasekPurchart\TracyBlueScreenBundle\DependencyInjection\ReplaceErrorControllerErrorRendererCompilerPass;

class TracyBlueScreenBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{

	/**
	 * @codeCoverageIgnore does not define any logic
	 *
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	public function build(ContainerBuilder $container): void
	{
		parent::build($container);

		$container->addCompilerPass(new ReplaceErrorControllerErrorRendererCompilerPass());
	}

}
