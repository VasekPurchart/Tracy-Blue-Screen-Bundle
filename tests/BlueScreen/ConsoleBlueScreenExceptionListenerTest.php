<?php

namespace VasekPurchart\TracyBlueScreenBundle\BlueScreen;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Tracy\Logger as TracyLogger;

use org\bovigo\vfs\vfsStream;

class ConsoleBlueScreenExceptionListenerTest extends \PHPUnit_Framework_TestCase
{

	public function testLogTracy()
	{
		vfsStream::setup('tracy');

		$command = $this->getMockBuilder(Command::class)->disableOriginalConstructor()->getMock();
		$input = $this->getMock(InputInterface::class);
		$output = $this->getMock(OutputInterface::class);
		$output
			->expects($this->once())
			->method('writeln')
			->with($this->stringContains('saved in file'));
		$exception = new \Exception('Foobar!');

		$event = new ConsoleExceptionEvent($command, $input, $output, $exception, 1);

		$logger = $this->getMockBuilder(TracyLogger::class)->disableOriginalConstructor()->getMock();

		$listener = new ConsoleBlueScreenExceptionListener($logger, vfsStream::url('tracy'), null);
		$listener->onConsoleException($event);
	}

	public function testUsesErrorOutputIfPossible()
	{
		vfsStream::setup('tracy');

		$command = $this->getMockBuilder(Command::class)->disableOriginalConstructor()->getMock();
		$input = $this->getMock(InputInterface::class);
		$errorOutput = $this->getMock(OutputInterface::class);
		$errorOutput
			->expects($this->once())
			->method('writeln')
			->with($this->stringContains('saved in file'));
		$output = $this->getMock(ConsoleOutputInterface::class);
		$output
			->expects($this->once())
			->method('getErrorOutput')
			->will($this->returnValue($errorOutput));

		$exception = new \Exception('Foobar!');

		$event = new ConsoleExceptionEvent($command, $input, $output, $exception, 1);

		$logger = $this->getMockBuilder(TracyLogger::class)->disableOriginalConstructor()->getMock();

		$listener = new ConsoleBlueScreenExceptionListener($logger, vfsStream::url('tracy'), null);
		$listener->onConsoleException($event);
	}

	public function testMissingLogDir()
	{
		$command = $this->getMockBuilder(Command::class)->disableOriginalConstructor()->getMock();
		$input = $this->getMock(InputInterface::class);
		$output = $this->getMock(OutputInterface::class);
		$exception = new \Exception('Foobar!');

		$event = new ConsoleExceptionEvent($command, $input, $output, $exception, 1);

		$logger = $this->getMockBuilder(TracyLogger::class)->disableOriginalConstructor()->getMock();

		$listener = new ConsoleBlueScreenExceptionListener($logger, null, null);

		$this->setExpectedException(\InvalidArgumentException::class);

		$listener->onConsoleException($event);
	}

}
