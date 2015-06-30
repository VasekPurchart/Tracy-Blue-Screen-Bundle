<?php

namespace VasekPurchart\TracyBlueScreenBundle\BlueScreen;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Tracy\BlueScreen;

class ControllerBlueScreenExceptionListenerTest extends \PHPUnit_Framework_TestCase
{

	public function testRenderTracy()
	{
		$kernel = $this->getMock(HttpKernelInterface::class);
		$request = new Request();
		$requestType = HttpKernelInterface::MASTER_REQUEST;
		$exception = new \Exception('Foobar!');

		$event = new GetResponseForExceptionEvent($kernel, $request, $requestType, $exception);

		$blueScreen = $this->getMock(BlueScreen::class);
		$blueScreen
			->expects($this->once())
			->method('render')
			->with($exception);

		$listener = new ControllerBlueScreenExceptionListener($blueScreen);
		$listener->onKernelException($event);
	}

}
