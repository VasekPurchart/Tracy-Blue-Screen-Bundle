<?php

namespace VasekPurchart\TracyBlueScreenBundle;

class TracyBlueScreenBundleTest extends \PHPUnit\Framework\TestCase
{

	public function testDependsOnTwig()
	{
		$bundle = new TracyBlueScreenBundle();
		$this->assertSame('TwigBundle', $bundle->getParent());
	}

}
