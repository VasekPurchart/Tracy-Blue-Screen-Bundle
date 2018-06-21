<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\DependencyInjection;

class TwigBundleRequiredException extends \Exception
{

	public function __construct(?\Throwable $previous = null)
	{
		parent::__construct('TwigBundle must be registered for this bundle to work properly', 0, $previous);
	}

}
