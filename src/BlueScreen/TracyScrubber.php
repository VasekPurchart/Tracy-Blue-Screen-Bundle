<?php

declare(strict_types=1);

namespace Cdn77\TracyBlueScreenBundle\BlueScreen;

interface TracyScrubber
{
    public function __invoke(string $key, mixed $value, string|null $class): bool;
}
