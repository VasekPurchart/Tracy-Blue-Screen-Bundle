<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\BlueScreen;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\BlueScreen;
use Tracy\Logger as TracyLogger;
use org\bovigo\vfs\vfsStream;

class ConsoleBlueScreenExceptionListenerTest extends \PHPUnit\Framework\TestCase
{

	public function testLogTracy(): void
	{
		vfsStream::setup('tracy');
		$directory = vfsStream::url('tracy');
		$file = $directory . '/exception.html';

		$command = $this->createMock(Command::class);
		$input = $this->createMock(InputInterface::class);
		$output = $this->createMock(OutputInterface::class);
		$output
			->expects($this->once())
			->method('writeln')
			->with($this->stringContains('saved in file'));
		$exception = new \Exception('Foobar!');

		$event = new ConsoleExceptionEvent($command, $input, $output, $exception, 1);

		$logger = $this->createMock(TracyLogger::class);
		$logger
			->expects($this->once())
			->method('getExceptionFile')
			->with($exception)
			->will($this->returnValue($file));

		$blueScreen = $this->createMock(BlueScreen::class);
		$blueScreen
			->expects($this->once())
			->method('renderToFile')
			->with($exception, $file);

		$listener = new ConsoleBlueScreenExceptionListener(
			$logger,
			$blueScreen,
			$directory,
			null
		);
		$listener->onConsoleException($event);
	}

	public function testUsesErrorOutputIfPossible(): void
	{
		vfsStream::setup('tracy');
		$directory = vfsStream::url('tracy');
		$file = $directory . '/exception.html';

		$command = $this->createMock(Command::class);
		$input = $this->createMock(InputInterface::class);
		$errorOutput = $this->createMock(OutputInterface::class);
		$errorOutput
			->expects($this->once())
			->method('writeln')
			->with($this->stringContains('saved in file'));
		$output = $this->createMock(ConsoleOutputInterface::class);
		$output
			->expects($this->once())
			->method('getErrorOutput')
			->will($this->returnValue($errorOutput));

		$exception = new \Exception('Foobar!');

		$event = new ConsoleExceptionEvent($command, $input, $output, $exception, 1);

		$logger = $this->createMock(TracyLogger::class);
		$logger
			->expects($this->once())
			->method('getExceptionFile')
			->with($exception)
			->will($this->returnValue($file));

		$blueScreen = $this->createMock(BlueScreen::class);
		$blueScreen
			->expects($this->once())
			->method('renderToFile')
			->with($exception, $file);

		$listener = new ConsoleBlueScreenExceptionListener(
			$logger,
			$blueScreen,
			$directory,
			null
		);
		$listener->onConsoleException($event);
	}

	public function testMissingLogDir(): void
	{
		$command = $this->createMock(Command::class);
		$input = $this->createMock(InputInterface::class);
		$output = $this->createMock(OutputInterface::class);
		$exception = new \Exception('Foobar!');

		$event = new ConsoleExceptionEvent($command, $input, $output, $exception, 1);

		$logger = $this->createMock(TracyLogger::class);
		$blueScreen = $this->createMock(BlueScreen::class);

		$listener = new ConsoleBlueScreenExceptionListener(
			$logger,
			$blueScreen,
			null,
			null
		);

		$this->expectException(\InvalidArgumentException::class);

		$listener->onConsoleException($event);
	}

}
