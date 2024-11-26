<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

use Symfony\Component\HttpKernel\Controller\ErrorController;

class CannotReplaceErrorRendererForNonDefaultErrorControllerException extends \Exception
{

	/** @var string */
	private $customErrorControllerClass;

	public function __construct(
		string $errorControllerClass,
		?\Throwable $previous = null
	)
	{
		parent::__construct(sprintf(
			'Automatic error renderer replacement is available only for default error controller (%s), currently %s is used.' . PHP_EOL
				. 'You can:' . PHP_EOL
				. '1) Use the default error controller (%s)' . PHP_EOL
				. '2) Use the provided BlueScreen error renderer in your current error controller (%s). Just pass the prepared `vasek_purchart.tracy_blue_screen.blue_screen.error_renderer` DI service.' . PHP_EOL
				. '3) If you do not want to use BlueScreen for requests, disable `controller` in this bundles configuration (you can still use the `console` part independently)',
			ErrorController::class,
			$errorControllerClass,
			ErrorController::class,
			$errorControllerClass
		), 0, $previous);

		$this->customErrorControllerClass = $errorControllerClass;
	}

	public function getCustomErrorControllerClass(): string
	{
		return $this->customErrorControllerClass;
	}

}
