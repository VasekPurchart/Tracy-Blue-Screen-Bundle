<?php

namespace VasekPurchart\TracyBlueScreenBundle\BlueScreen;

use ReflectionClass;

use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Tracy\Logger as TracyLogger;

class ConsoleBlueScreenExceptionListener
{

	/** @var \Tracy\Logger */
	private $tracyLogger;

	public function __construct(
		TracyLogger $tracyLogger
	)
	{
		$this->tracyLogger = $tracyLogger;
	}

	public function onConsoleException(ConsoleExceptionEvent $event)
	{
		if (
			$this->tracyLogger->directory === null
			|| !is_dir($this->tracyLogger->directory)
			|| !is_writable($this->tracyLogger->directory)
		) {
			throw new \InvalidArgumentException(sprintf(
				'Log directory must be a writable directory, %s [%s] given',
				$this->tracyLogger->directory,
				gettype($this->tracyLogger->directory)
			), 0, $event->getException());
		}

		$loggerReflection = new ReflectionClass($this->tracyLogger);
		$exceptionFileMethodReflection = $loggerReflection->getMethod('logException');
		$exceptionFileMethodReflection->setAccessible(true);
		$exceptionFile = $exceptionFileMethodReflection->invoke($this->tracyLogger, $event->getException());

		$output = $event->getOutput();
		$this->printErrorMessage($output, sprintf('BlueScreen saved in file: %s', $exceptionFile));
	}

	/**
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @param string $message
	 */
	private function printErrorMessage(OutputInterface $output, $message)
	{
		$message = sprintf('<error>%s</error>', $message);
		if ($output instanceof ConsoleOutputInterface) {
			$output->getErrorOutput()->writeln($message);
		} else {
			$output->writeln($message);
		}
	}

}
