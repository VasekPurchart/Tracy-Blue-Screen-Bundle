<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\BlueScreen;

use Generator;
use PHPUnit\Framework\Assert;
use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Tracy\BlueScreen;

class BlueScreenErrorRendererTest extends \PHPUnit\Framework\TestCase
{

	public function throwableDataProvider(): Generator
	{
		yield 'Exception' => (static function (): array {
			$message = 'Foobar!';

			return [
				'throwable' => new \Exception($message),
				'message' => $message,
			];
		})();

		yield 'Error' => (static function (): array {
			$message = 'Foobar!';

			return [
				'throwable' => new \Exception($message),
				'message' => $message,
			];
		})();

		yield 'notice' => [ // relies on ErrorHandler to be converted to a Throwable based error
			'throwable' => (static function (): \Throwable {
				try {
					$foo[0];
				} catch (\Throwable $e) {
					return $e;
				}
			})(),
			'message' => 'Notice: Undefined variable: foo',
		];
	}

	/**
	 * @dataProvider throwableDataProvider
	 *
	 * @param \Throwable $throwable
	 * @param string $message
	 */
	public function testRenderTracy(
		\Throwable $throwable,
		string $message
	): void
	{
		$blueScreen = $this->createMock(BlueScreen::class);
		$blueScreen
			->expects(self::once())
			->method('render')
			->with($throwable);

		$fallbackErrorRenderer = $this->createMock(ErrorRendererInterface::class);
		$fallbackErrorRenderer
			->expects(self::never())
			->method('render');

		$errorRenderer = new BlueScreenErrorRenderer($blueScreen, true, $fallbackErrorRenderer);
		$flattenException = $errorRenderer->render($throwable);

		Assert::assertSame($message, $flattenException->getMessage());
	}

	public function renderUsingFallbackDataProvider(): Generator
	{
		foreach ($this->throwableDataProvider() as $caseName => $caseData) {
			yield 'debug: false + ' . $caseName => [
				'debug' => false,
				'throwable' => $caseData['throwable'],
			];

			yield 'debug: Closure evaluated as false + ' . $caseName => [
				'debug' => static function (): bool {
					return false;
				},
				'throwable' => $caseData['throwable'],
			];
		}
	}

	/**
	 * @dataProvider renderUsingFallbackDataProvider
	 *
	 * @param bool|\Closure $debug
	 * @param \Throwable $throwable
	 */
	public function testRenderUsingFallback(
		$debug,
		\Throwable $throwable
	): void
	{
		$blueScreen = $this->createMock(BlueScreen::class);
		$blueScreen
			->expects(self::never())
			->method('render');

		$fallbackErrorRenderer = $this->createMock(ErrorRendererInterface::class);
		$fallbackErrorRenderer
			->expects(self::once())
			->method('render')
			->with($throwable);

		$errorRenderer = new BlueScreenErrorRenderer($blueScreen, $debug, $fallbackErrorRenderer);
		$errorRenderer->render($throwable);
	}

}
