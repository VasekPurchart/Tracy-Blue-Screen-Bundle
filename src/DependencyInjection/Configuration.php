<?php

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements \Symfony\Component\Config\Definition\ConfigurationInterface
{

	const PARAMETER_CONTROLLER_LISTENER_PRIORITY = 'listener_priority';

	const SECTION_CONTROLLER = 'controller';

	/** @var string */
	private $rootNode;

	/**
	 * @param string $rootNode
	 */
	public function __construct($rootNode)
	{
		$this->rootNode = $rootNode;
	}

	/**
	 * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root($this->rootNode);

		$rootNode
			->children()
				->arrayNode(self::SECTION_CONTROLLER)
					->addDefaultsIfNotSet()
					->children()
						->integerNode(self::PARAMETER_CONTROLLER_LISTENER_PRIORITY)
							->info('Priority with which the listener will be registered.')
							->defaultValue(0)
							->end()
						->end()
					->end()
				->end()
			->end();

		return $treeBuilder;
	}

}
