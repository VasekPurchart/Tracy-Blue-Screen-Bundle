<?php

declare(strict_types=1);

namespace Cdn77\TracyBlueScreenBundle\BlueScreen;

use Tracy\BlueScreen;
use Tracy\Debugger;

use function array_merge;

final class BlueScreenFactory
{
    /**
     * @param string[] $collapsePaths
     * @param (callable(string, mixed, string|null): bool)|null $scrubber
     */
    public static function create(array $collapsePaths, callable|null $scrubber = null): BlueScreen
    {
        $blueScreen = Debugger::getBlueScreen();
        $blueScreen->collapsePaths = array_merge($blueScreen->collapsePaths, $collapsePaths);

        if ($scrubber !== null) {
            $blueScreen->scrubber = $scrubber;
        }

        return $blueScreen;
    }
}
