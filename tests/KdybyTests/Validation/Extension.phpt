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
use Symfony\Component\Validator\Constraints as Assert;
use Tester;

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
		$config->addConfig(__DIR__ . '/../nette-reset.neon', !isset($config->defaultExtensions['nette']) ? 'v23' : 'v22');

		return $config->createContainer();
	}



	public function testFunctionality()
	{
		$container = $this->createContainer();

		/** @var Symfony\Component\Validator\Validator\ValidatorInterface $validator */
		$validator = $container->getByType('Symfony\Component\Validator\Validator\ValidatorInterface');
		Tester\Assert::true($validator instanceof Symfony\Component\Validator\Validator\RecursiveValidator);

		$article = new ArticleMock();

		/** @var Symfony\Component\Validator\ConstraintViolationInterface[] $violations */
		$violations = $validator->validate($article);
		Tester\Assert::same(1, count($violations));
		Tester\Assert::same('This value should not be null.', $violations[0]->getMessage());

		$article->title = "Nette Framework + Symfony/Validator";

		/** @var Symfony\Component\Validator\ConstraintViolationInterface[] $violations */
		$violations = $validator->validate($article);
		Tester\Assert::same(0, count($violations));
	}

}


class ArticleMock extends Nette\Object
{

	/**
	 * @Assert\NotNull()
	 * @var string
	 */
	public $title;

}

\run(new ExtensionTest());
