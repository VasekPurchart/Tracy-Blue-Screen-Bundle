<?php

namespace VasekPurchart\TracyBlueScreenBundle\BlueScreen;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

use Tracy\Debugger;

class ControllerBlueScreenExceptionListener
{

	/** @var string */
	private $environment;

	/** @var boolean */
	private $debug;

	/**
	 * @param string $environment
	 * @param boolean $debug
	 */
	public function __construct($environment, $debug)
	{
		$this->environment = $environment;
		$this->debug = $debug;
	}

	public function onKernelException(GetResponseForExceptionEvent $event)
	{
		if ($this->environment !== 'dev' || $this->debug === false) {
			return;
		}

		$this->forceExceptionControllerHtml($event->getRequest());
		$this->renderBlueScreen($event->getException());
	}

	private function forceExceptionControllerHtml(Request $request)
	{
		$request->setRequestFormat('html');
		$request->attributes->set('_format', 'html');
	}

	private function renderBlueScreen(\Exception $exception)
	{
		if (!headers_sent()) {
			$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
			$code = isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE ') !== false ? 503 : 500;
			header($protocol . ' ' . $code, true, $code);
			header('Content-Type: text/html; charset=UTF-8');
		}

		Debugger::getBlueScreen()->render($exception);
	}

}
