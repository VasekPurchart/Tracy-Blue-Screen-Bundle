<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Tracy\Logger as TracyLogger;

class Configuration implements \Symfony\Component\Config\Definition\ConfigurationInterface
{

	public const PARAMETER_COLLAPSE_PATHS = 'collapse_paths';
	public const PARAMETER_CONSOLE_BROWSER = 'browser';
	public const PARAMETER_CONSOLE_ENABLED = 'enabled';
	public const PARAMETER_CONSOLE_LISTENER_PRIORITY = 'listener_priority';
	public const PARAMETER_CONSOLE_LOG_DIRECTORY = 'log_directory';
	public const PARAMETER_CONTROLLER_ENABLED = 'enabled';
	public const PARAMETER_CONTROLLER_LISTENER_PRIORITY = 'listener_priority';

	public const SECTION_BLUE_SCREEN = 'blue_screen';
	public const SECTION_CONSOLE = 'console';
	public const SECTION_CONTROLLER = 'controller';

	/** @var string */
	private $rootNode;

	/** @var string */
	private $kernelRootDir;

	/** @var string */
	private $kernelLogsDir;

	/** @var string */
	private $kernelCacheDir;

	public function __construct(
		string $rootNode,
		string $kernelRootDir,
		string $kernelLogsDir,
		string $kernelCacheDir
	)
	{
		$this->rootNode = $rootNode;
		$this->kernelRootDir = $kernelRootDir;
		$this->kernelLogsDir = $kernelLogsDir;
		$this->kernelCacheDir = $kernelCacheDir;
	}

	public function getConfigTreeBuilder(): TreeBuilder
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root($this->rootNode);

		$rootNode->children()->append($this->createControllerNode(self::SECTION_CONTROLLER));
		$rootNode->children()->append($this->createConsoleNode(self::SECTION_CONSOLE));
		$rootNode->children()->append($this->createBlueScreenNode(self::SECTION_BLUE_SCREEN));

		return $treeBuilder;
	}

	private function createControllerNode(string $nodeName): ArrayNodeDefinition
	{
		$node = new ArrayNodeDefinition($nodeName);
		$node->addDefaultsIfNotSet();
		$node->children()->append($this->createDebugNode(self::PARAMETER_CONTROLLER_ENABLED, 'Enable debug screen for controllers.'));
		$node->children()->append($this->createPriorityNode(self::PARAMETER_CONTROLLER_LISTENER_PRIORITY));

		return $node;
	}

	private function createDebugNode(string $nodeName, string $info): ScalarNodeDefinition
	{
		$node = new ScalarNodeDefinition($nodeName);
		$node->info($info);
		$node->defaultNull();

		return $node;
	}

	private function createPriorityNode(string $nodeName): IntegerNodeDefinition
	{
		$node = new IntegerNodeDefinition($nodeName);
		$node->info('Priority with which the listener will be registered.');
		$node->defaultValue(0);

		return $node;
	}

	private function createConsoleNode(string $nodeName): ArrayNodeDefinition
	{
		$node = new ArrayNodeDefinition($nodeName);
		$node->addDefaultsIfNotSet();
		$node->children()->append($this->createDebugNode(self::PARAMETER_CONSOLE_ENABLED, 'Enable debug screen for console.'));
		$node->children()->append($this->createConsoleLogDirectoryNode(self::PARAMETER_CONSOLE_LOG_DIRECTORY));
		$node->children()->append($this->createConsoleBrowserNode(self::PARAMETER_CONSOLE_BROWSER));
		$node->children()->append($this->createPriorityNode(self::PARAMETER_CONSOLE_LISTENER_PRIORITY));

		return $node;
	}

	private function createConsoleLogDirectoryNode(string $nodeName): ScalarNodeDefinition
	{
		$node = new ScalarNodeDefinition($nodeName);
		$node->info(
			'Directory, where BlueScreens for console will be stored.'
			. ' If you are already using Tracy for logging, set this to the same.'
			. sprintf(' This will be only used, if given %s instance does not have a directory set.', TracyLogger::class)
		);
		$node->defaultValue($this->kernelLogsDir);

		return $node;
	}

	private function createConsoleBrowserNode(string $nodeName): ScalarNodeDefinition
	{
		$node = new ScalarNodeDefinition($nodeName);
		$node->info(
			'Configure this to open generated BlueScreen in your browser.'
			. ' Configuration option may be for example \'google-chrome\' or \'firefox\''
			. ' and it will be invoked as a shell command.'
		);
		$node->defaultNull();

		return $node;
	}

	private function createBlueScreenNode(string $nodeName): ArrayNodeDefinition
	{
		$node = new ArrayNodeDefinition($nodeName);
		$node->addDefaultsIfNotSet();
		$node->children()->append($this->createBlueScreenCollapsePathsNode(self::PARAMETER_COLLAPSE_PATHS));

		return $node;
	}

	private function createBlueScreenCollapsePathsNode(string $nodeName): ArrayNodeDefinition
	{
		$node = new ArrayNodeDefinition($nodeName);
		$node->info('Add paths which should be collapsed (for external/compiled code) so that actual error is expanded.');
		$node->prototype('scalar');
		$node->defaultValue([
			$this->kernelRootDir . '/bootstrap.php.cache',
			$this->kernelCacheDir,
		]);

		return $node;
	}

}
