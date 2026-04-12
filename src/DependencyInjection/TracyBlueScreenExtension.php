<?php

declare(strict_types=1);

namespace Cdn77\TracyBlueScreenBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

use function assert;
use function is_bool;
use function is_string;

//phpcs:disable SlevomatCodingStandard.Files.LineLength.LineTooLong
final class TracyBlueScreenExtension extends ConfigurableExtension
{
    public const string ContainerParameterBlueScreenCollapsePaths = 'cdn77.tracy_blue_screen.blue_screen.collapse_paths';
    public const string ContainerParameterConsoleBrowser = 'cdn77.tracy_blue_screen.console.browser';
    public const string ContainerParameterConsoleListenerPriority = 'cdn77.tracy_blue_screen.console.listener_priority';
    public const string ContainerParameterConsoleLogDirectory = 'cdn77.tracy_blue_screen.console.log_directory';
    public const string ContainerParameterControllerListenerPriority = 'cdn77.tracy_blue_screen.controller.listener_priority';

    /** @param mixed[] $mergedConfig */
    // phpstan-ignore-next-line missingType.iterableValue (parent has untyped array)
    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        /** @var array{collapse_paths: list<string>, scrubber: string|null} $blueScreenConfig */
        $blueScreenConfig = $mergedConfig[Configuration::SectionBlueScreen];
        /** @var array{enabled: bool|null, browser: string|null, listener_priority: int, log_directory: string} $consoleConfig */
        $consoleConfig = $mergedConfig[Configuration::SectionConsole];
        /** @var array{enabled: bool|null, listener_priority: int} $controllerConfig */
        $controllerConfig = $mergedConfig[Configuration::SectionController];

        $container->setParameter(
            self::ContainerParameterBlueScreenCollapsePaths,
            $blueScreenConfig[Configuration::ParameterCollapsePaths],
        );
        $container->setParameter(
            self::ContainerParameterConsoleBrowser,
            $consoleConfig[Configuration::ParameterConsoleBrowser],
        );
        $container->setParameter(
            self::ContainerParameterConsoleListenerPriority,
            $consoleConfig[Configuration::ParameterConsoleListenerPriority],
        );
        $container->setParameter(
            self::ContainerParameterConsoleLogDirectory,
            $consoleConfig[Configuration::ParameterConsoleLogDirectory],
        );
        $container->setParameter(
            self::ContainerParameterControllerListenerPriority,
            $controllerConfig[Configuration::ParameterControllerListenerPriority],
        );

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/config'));
        $loader->load('services.yml');

        $environment = $container->getParameter('kernel.environment');
        assert(is_string($environment));
        $debug = $container->getParameter('kernel.debug');
        assert(is_bool($debug));

        if (
            $this->isEnabled(
                $consoleConfig[Configuration::ParameterConsoleEnabled],
                $environment,
                $debug,
            )
        ) {
            $loader->load('console_listener.yml');
        }

        if (
            ! $this->isEnabled(
                $controllerConfig[Configuration::ParameterControllerEnabled],
                $environment,
                $debug,
            )
        ) {
            return;
        }

        $loader->load('controller_listener.yml');
    }

    /** @param mixed[] $config */
    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        return new Configuration($this->getAlias());
    }

    private function isEnabled(bool|null $configOption, string $environment, bool $debug): bool
    {
        if ($configOption === null) {
            return $environment === 'dev' && $debug === true;
        }

        return $configOption;
    }
}
