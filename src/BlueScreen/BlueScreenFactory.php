<?php

declare(strict_types = 1);

namespace VasekPurchart\TracyBlueScreenBundle\BlueScreen;

use Tracy\BlueScreen;
use Tracy\Debugger;

class BlueScreenFactory
{

	/**
	 * @param string[] $collapsePaths
	 * @return \Tracy\BlueScreen
	 */
	public static function create(array $collapsePaths): BlueScreen
	{
		$blueScreen = Debugger::getBlueScreen();
		$blueScreen->collapsePaths = array_merge($blueScreen->collapsePaths, $collapsePaths);

		return $blueScreen;
	}

}
