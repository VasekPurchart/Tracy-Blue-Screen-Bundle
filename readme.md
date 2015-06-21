Tracy BlueScreen Bundle
======================

**This bundle lets you use the [Tracy's debug screen](https://github.com/nette/tracy#visualization-of-errors-and-exceptions) in combination with the the default profiler in your Symfony application.**

Why is Tracy's debug screen better than the Symfony default exception screen:

* You can browse all values of function call arguments.
* All information about the current request and environment.
* You can view all the information which is contained by the exception (e.g. private properties).
* Configurable links to files in stacktrace which can open directly in the IDE.
* Fullscreen layout providing more space for information.
* Look at the interactive [example screen](http://nette.github.io/tracy/tracy-exception.html).

However the Symfony profiler provides a lot of useful information about the application when an error occurs, so it is better to have them both available:

![Nette Tracy with Symfony profiler screenshot](docs/tracy-with-profiler.png)

Usage
-----

If you do not have any custom `kernel.exception` listeners this works out of the box. However if you have any, you have to ensure that they do not return any response, because that prevents the profiler from showing up (the same applies for the default Symfony exception screen).

This bundle expects that you are using the default Symfony profiler screen rendered via the [TwigBundle](http://symfony.com/doc/current/reference/configuration/twig.html), which must be registered.

Installation
-----------

Install package [`vasek-purchart/tracy-blue-screen-bundle`](https://packagist.org/packages/vasek-purchart/tracy-blue-screen-bundle) with [Composer](https://getcomposer.org/):

```bash
composer require --dev vasek-purchart/tracy-blue-screen-bundle
```

Register the bundle in your application kernel only for `dev` or `test` environments:
```php
// app/AppKernel.php
public function registerBundles()
{
	// ...
	if (in_array($this->getEnvironment(), array('dev', 'test'))) {
		$bundles[] = new VasekPurchart\TracyBlueScreenBundle\TracyBlueScreenBundle();
	}

	return $bundles;
}
```

