<?php

declare(strict_types = 1);

use Symfony\Component\ErrorHandler\ErrorHandler;

error_reporting(E_ALL);

require __DIR__ . '/../vendor/autoload.php';

ErrorHandler::register();
