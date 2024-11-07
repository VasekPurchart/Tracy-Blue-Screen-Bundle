<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\BlueScreen;

use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Tracy\BlueScreen;

class BlueScreenErrorRenderer implements \Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface
{

	/** @var \Tracy\BlueScreen */
	private $blueScreen;

	/** @var bool|\Closure */
	private $debug;

	/** @var \Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface */
	private $fallbackErrorRenderer;

	/**
	 * @param \Tracy\BlueScreen $blueScreen
	 * @param bool|\Closure $debug
	 * @param \Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface $fallbackErrorRenderer
	 */
	public function __construct(
		BlueScreen $blueScreen,
		$debug,
		ErrorRendererInterface $fallbackErrorRenderer
	)
	{
		$this->blueScreen = $blueScreen;
		$this->debug = $debug;
		$this->fallbackErrorRenderer = $fallbackErrorRenderer;
	}

	public function render(\Throwable $exception): \Symfony\Component\ErrorHandler\Exception\FlattenException
	{
		if (!$this->isDebug()) {
			return $this->fallbackErrorRenderer->render($exception);
		}

		return \Symfony\Component\ErrorHandler\Exception\FlattenException::createFromThrowable($exception)
			->setAsString($this->getBlueScreenAsString($exception));
	}

	private function getBlueScreenAsString(\Throwable $exception): string
	{
		ob_start();
		ob_start(); // double buffer prevents sending HTTP headers in some PHP

		$this->blueScreen->render($exception);

		$result = ob_get_clean();
		ob_end_clean();

		return $result;
	}

	private function isDebug(): bool
	{
		return is_bool($this->debug) ? $this->debug : ($this->debug)();
	}

}
