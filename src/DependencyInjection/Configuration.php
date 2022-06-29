<?php

namespace NorthernIndustry\TimeMachineBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {

	public function getConfigTreeBuilder(): TreeBuilder {
		$treeBuilder = new TreeBuilder('time_machine');

		$rootNode = $treeBuilder->getRootNode();

		$rootNode
			->children()
				->booleanNode('enabled')->defaultFalse()->end()
				->arrayNode('entities')
					->scalarPrototype()->end()
				->end()
				->arrayNode('ignored_columns')
					->scalarPrototype()->end()
				->end()
			->end();

		return $treeBuilder;
	}
}