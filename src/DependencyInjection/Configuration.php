<?php

declare(strict_types=1);

namespace Cdn77\TracyBlueScreenBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Tracy\Logger as TracyLogger;

use function sprintf;

final class Configuration implements ConfigurationInterface
{
    public const string ParameterCollapsePaths = 'collapse_paths';
    public const string ParameterScrubber = 'scrubber';
    public const string ParameterConsoleBrowser = 'browser';
    public const string ParameterConsoleEnabled = 'enabled';
    public const string ParameterConsoleListenerPriority = 'listener_priority';
    public const string ParameterConsoleLogDirectory = 'log_directory';
    public const string ParameterControllerEnabled = 'enabled';
    public const string ParameterControllerListenerPriority = 'listener_priority';

    public const string SectionBlueScreen = 'blue_screen';
    public const string SectionConsole = 'console';
    public const string SectionController = 'controller';

    public function __construct(private readonly string $alias)
    {
    }

    /** @return TreeBuilder<'array'> */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder($this->alias);
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode(self::SectionController)
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode(self::ParameterControllerEnabled)
                            ->info('Enable debug screen for controllers.')
                            ->defaultNull()
                            ->end()
                        ->integerNode(self::ParameterControllerListenerPriority)
                            ->info('Priority with which the listener will be registered.')
                            ->defaultValue(0)
                            ->end()
                        ->end()
                    ->end()
                ->arrayNode(self::SectionConsole)
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode(self::ParameterConsoleEnabled)
                            ->info('Enable debug screen for console.')
                            ->defaultNull()
                            ->end()
                        ->scalarNode(self::ParameterConsoleLogDirectory)
                            ->info(
                                'Directory, where BlueScreens for console will be stored.'
                                . ' If you are already using Tracy for logging, set this to the same.'
                                . sprintf(
                                    ' This will be only used, if given %s instance does not have a directory set.',
                                    TracyLogger::class,
                                ),
                            )
                            ->defaultValue('%kernel.logs_dir%')
                            ->end()
                        ->scalarNode(self::ParameterConsoleBrowser)
                            ->info(
                                'Configure this to open generated BlueScreen in your browser.'
                                . ' Configuration option may be for example \'google-chrome\' or \'firefox\''
                                . ' and it will be invoked as a shell command.',
                            )
                            ->defaultNull()
                            ->end()
                        ->integerNode(self::ParameterConsoleListenerPriority)
                            ->info('Priority with which the listener will be registered.')
                            ->defaultValue(0)
                            ->end()
                        ->end()
                    ->end()
                ->arrayNode(self::SectionBlueScreen)
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode(self::ParameterCollapsePaths)
                            //phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
                            ->info('Add paths which should be collapsed (for external/compiled code) so that actual error is expanded.')
                            ->prototype('scalar')
                                ->end()
                            ->defaultValue([
                                '%kernel.project_dir%/bootstrap.php.cache',
                                '%kernel.cache_dir%',
                            ])
                            ->end()
                        ->scalarNode(self::ParameterScrubber)
                            //phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong
                            ->info('Service ID of a callable(string $key, mixed $value, ?string $class): bool that returns true for sensitive data to hide.')
                            ->defaultNull()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
