<?php

namespace VasekPurchart\TracyBlueScreenBundle\BlueScreen;

use Tracy\Debugger;

class BlueScreenFactory
{

	/**
	 * @param string[] $collapsePaths
	 * @return \Tracy\BlueScreen
	 */
	public static function create(array $collapsePaths)
	{
		$blueScreen = Debugger::getBlueScreen();
		$blueScreen->collapsePaths = array_merge($blueScreen->collapsePaths, $collapsePaths);

		return $blueScreen;
	}

}
