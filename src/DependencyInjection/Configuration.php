<?php

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements \Symfony\Component\Config\Definition\ConfigurationInterface
{

	/**
	 * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
	 */
	public function getConfigTreeBuilder()
	{
		return new TreeBuilder();
	}

}
