<?php

/**
 * Test: Kdyby\Validation\Extension.
 *
 * @testCase Kdyby\Validation\ExtensionTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Validation
 */

namespace KdybyTests\Validation;

use Kdyby;
use Nette;
use Symfony;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ExtensionTest extends Tester\TestCase
{

	/**
	 * @return \SystemContainer|Nette\DI\Container
	 */
	public function createContainer()
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);
		$config->addParameters(array('container' => array('class' => 'SystemContainer_' . md5(time()))));
		$config->addConfig(__DIR__ . '/../nette-reset.neon');

		return $config->createContainer();
	}



	public function testFunctionality()
	{
		$container = $this->createContainer();
		$validator = $container->getByType('Symfony\Component\Validator\ValidatorInterface');
		Assert::true($validator instanceof Symfony\Component\Validator\Validator);
	}

}

\run(new ExtensionTest());
