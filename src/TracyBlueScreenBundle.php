<?php

namespace VasekPurchart\TracyBlueScreenBundle;

class TracyBlueScreenBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{

	/**
	 * @return string
	 */
	public function getParent()
	{
		return 'TwigBundle';
	}

}
