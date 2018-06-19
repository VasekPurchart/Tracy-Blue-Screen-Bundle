<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle;

class TracyBlueScreenBundleTest extends \PHPUnit\Framework\TestCase
{

	public function testDependsOnTwig(): void
	{
		$bundle = new TracyBlueScreenBundle();
		$this->assertSame('TwigBundle', $bundle->getParent());
	}

}
