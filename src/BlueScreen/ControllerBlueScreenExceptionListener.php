<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\BlueScreen;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

use Tracy\BlueScreen;

class ControllerBlueScreenExceptionListener
{

	/** @var \Tracy\BlueScreen */
	private $blueScreen;

	public function __construct(
		BlueScreen $blueScreen
	)
	{
		$this->blueScreen = $blueScreen;
	}

	public function onKernelException(GetResponseForExceptionEvent $event)
	{
		$this->forceExceptionControllerHtml($event->getRequest());
		$this->renderBlueScreen($event->getException());
	}

	private function forceExceptionControllerHtml(Request $request)
	{
		$request->setRequestFormat('html');
		$request->attributes->set('_format', 'html');
	}

	private function renderBlueScreen(\Throwable $exception)
	{
		if (!headers_sent()) {
			// @codeCoverageIgnoreStart
			// sends output and uses global state
			$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
			$code = isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE ') !== false ? 503 : 500;
			header($protocol . ' ' . $code, true, $code);
			header('Content-Type: text/html; charset=UTF-8');
		}
		// @codeCoverageIgnoreEnd

		$this->blueScreen->render($exception);
	}

}
