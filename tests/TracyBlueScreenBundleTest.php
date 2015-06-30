<?php

namespace VasekPurchart\TracyBlueScreenBundle;

class TracyBlueScreenBundleTest extends \PHPUnit_Framework_TestCase
{

	public function testDependsOnTwig()
	{
		$bundle = new TracyBlueScreenBundle();
		$this->assertSame('TwigBundle', $bundle->getParent());
	}

}
