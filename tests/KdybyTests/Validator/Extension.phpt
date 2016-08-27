<?php

/**
 * Test: Kdyby\Validator\DI\ValidatorExtension.
 *
 * @testCase Kdyby\Validator\ExtensionTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Validator
 */

namespace KdybyTests\Validator;

use Kdyby;
use KdybyTests\ValidatorMocks\ArticleMock;
use Nette;
use Symfony;
use Tester;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ExtensionTest extends Tester\TestCase
{

	/**
	 * @return Nette\DI\Container
	 */
	public function createContainer(array $files = [])
	{
		$rootDir = __DIR__ . '/..';

		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR)
			->addParameters([
				'appDir' => $rootDir,
			]);
		$config->addConfig(__DIR__ . '/../nette-reset.neon');
		foreach ($files as $file) {
			$config->addConfig($file);
		}

		return $config->createContainer();
	}



	public function testFunctionality()
	{
		$container = $this->createContainer([__DIR__ . '/config/annotations.neon']);

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



	public function testWithoutAnnotations()
	{
		$container = $this->createContainer([__DIR__ . '/config/custom-loader.neon']);

		/** @var Symfony\Component\Validator\Validator\ValidatorInterface $validator */
		$validator = $container->getByType('Symfony\Component\Validator\Validator\ValidatorInterface');
		Tester\Assert::true($validator instanceof Symfony\Component\Validator\Validator\RecursiveValidator);

		$article = new ArticleMock();

		/** @var Symfony\Component\Validator\ConstraintViolationInterface[] $violations */
		$violations = $validator->validate($article);
		Tester\Assert::same(0, count($violations));

		$article->title = "Nette Framework + Symfony/Validator";

		/** @var Symfony\Component\Validator\ConstraintViolationInterface[] $violations */
		$violations = $validator->validate($article);
		Tester\Assert::same(1, count($violations));
		Tester\Assert::same('This value is not a valid email address.', $violations[0]->getMessage());
	}



	public function testConstraintValidatorFactory()
	{
		$container = $this->createContainer();

		$factory = $container->getByType('Symfony\Component\Validator\ConstraintValidatorFactoryInterface');

		// Validator without dependeny (created without DIC).
		Tester\Assert::type('Symfony\Component\Validator\Constraints\BlankValidator', $factory->getInstance(new \Symfony\Component\Validator\Constraints\Blank()));

		// ExpressionValidator (requires a special fix).
		Tester\Assert::type('Symfony\Component\Validator\Constraints\ExpressionValidator', $factory->getInstance(new \Symfony\Component\Validator\Constraints\Expression(['expression' => ''])));

		// Custom validator with dependency (haa to be created by DIC).
		Tester\Assert::type('KdybyTests\ValidatorMocks\FooConstraintValidator', $factory->getInstance(new \KdybyTests\ValidatorMocks\FooConstraint()));
	}



	public function strictEmailDataProvider()
	{
		return [
			[[], FALSE],
			[[__DIR__ . '/config/strict-email.neon'], TRUE],
			[[__DIR__ . '/config/non-strict-email.neon'], FALSE],
		];
	}



	/**
	 * @dataProvider strictEmailDataProvider
	 */
	public function testStrictEmail($configFiles, $strict)
	{
		$container = $this->createContainer($configFiles);

		$factory = $container->getByType('Symfony\Component\Validator\ConstraintValidatorFactoryInterface');

		$validator = $factory->getInstance(new \Symfony\Component\Validator\Constraints\Email());
		Tester\Assert::type('Symfony\Component\Validator\Constraints\EmailValidator', $validator);

		$property = new \ReflectionProperty('Symfony\Component\Validator\Constraints\EmailValidator', 'isStrict');
		$property->setAccessible(TRUE);
		Tester\Assert::same($strict, $property->getValue($validator));
	}

}



\run(new ExtensionTest());
