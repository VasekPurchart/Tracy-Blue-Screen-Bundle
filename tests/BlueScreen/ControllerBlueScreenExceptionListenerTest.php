<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\BlueScreen;

use Generator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tracy\BlueScreen;

class ControllerBlueScreenExceptionListenerTest extends \PHPUnit\Framework\TestCase
{

	public function throwableDataProvider(): Generator
	{
		yield 'Exception' => [
			'throwable' => new \Exception('Foobar!'),
		];

		yield 'Error' => [
			'throwable' => new \Error(),
		];

		yield 'notice' => [ // relies on ErrorHandler to be converted to a Throwable based error
			'throwable' => (static function (): \Throwable {
				try {
					$foo[0];
				} catch (\Throwable $e) {
					return $e;
				}
			})(),
		];
	}

	/**
	 * @dataProvider throwableDataProvider
	 *
	 * @param \Throwable $throwable
	 */
	public function testRenderTracy(
		\Throwable $throwable
	): void
	{
		$kernel = $this->createMock(HttpKernelInterface::class);
		$request = new Request();
		$requestType = HttpKernelInterface::MASTER_REQUEST;

		$event = new ExceptionEvent($kernel, $request, $requestType, $throwable);

		$blueScreen = $this->createMock(BlueScreen::class);
		$blueScreen
			->expects(self::once())
			->method('render')
			->with(self::callback(static function (\Throwable $passedThrowable) use ($throwable) {
				if ($passedThrowable instanceof \Symfony\Component\Debug\Exception\FatalThrowableError) {
					return $passedThrowable->getOriginalClassName() === get_class($throwable)
						&& $passedThrowable->getMessage() === $throwable->getMessage()
						&& $passedThrowable->getCode() === $throwable->getCode();
				}

				return $passedThrowable === $throwable;
			}));

		$listener = new ControllerBlueScreenExceptionListener($blueScreen);
		$listener->onKernelException($event);
	}

}
