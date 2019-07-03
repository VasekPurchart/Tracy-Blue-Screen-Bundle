<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

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
		$treeBuilder = new TreeBuilder($this->rootNode);
		$rootNode = $treeBuilder->getRootNode();

		$rootNode
			->children()
				->arrayNode(self::SECTION_CONTROLLER)
					->addDefaultsIfNotSet()
					->children()
						->scalarNode(self::PARAMETER_CONTROLLER_ENABLED)
							->info('Enable debug screen for controllers.')
							->defaultNull()
							->end()
						->integerNode(self::PARAMETER_CONTROLLER_LISTENER_PRIORITY)
							->info('Priority with which the listener will be registered.')
							->defaultValue(0)
							->end()
						->end()
					->end()
				->arrayNode(self::SECTION_CONSOLE)
					->addDefaultsIfNotSet()
					->children()
						->scalarNode(self::PARAMETER_CONSOLE_ENABLED)
							->info('Enable debug screen for console.')
							->defaultNull()
							->end()
						->scalarNode(self::PARAMETER_CONSOLE_LOG_DIRECTORY)
							->info(
								'Directory, where BlueScreens for console will be stored.'
								. ' If you are already using Tracy for logging, set this to the same.'
								. sprintf(' This will be only used, if given %s instance does not have a directory set.', TracyLogger::class)
							)
							->defaultValue($this->kernelLogsDir)
							->end()
						->scalarNode(self::PARAMETER_CONSOLE_BROWSER)
							->info(
								'Configure this to open generated BlueScreen in your browser.'
								. ' Configuration option may be for example \'google-chrome\' or \'firefox\''
								. ' and it will be invoked as a shell command.'
							)
							->defaultNull()
							->end()
						->integerNode(self::PARAMETER_CONSOLE_LISTENER_PRIORITY)
							->info('Priority with which the listener will be registered.')
							->defaultValue(0)
							->end()
						->end()
					->end()
				->arrayNode(self::SECTION_BLUE_SCREEN)
					->addDefaultsIfNotSet()
					->children()
						->arrayNode(self::PARAMETER_COLLAPSE_PATHS)
							->info('Add paths which should be collapsed (for external/compiled code) so that actual error is expanded.')
							->prototype('scalar')
								->end()
							->defaultValue([
								$this->kernelRootDir . '/bootstrap.php.cache',
								$this->kernelCacheDir,
							])
							->end()
						->end()
					->end()
				->end()
			->end();

		return $treeBuilder;
	}

}
