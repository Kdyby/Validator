<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Validator;

use Kdyby;
use Nette;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ConstraintValidatorFactory extends Nette\Object implements ConstraintValidatorFactoryInterface
{

	/**
	 * @var Nette\DI\Container
	 */
	private $serviceLocator;

	/**
	 * @var array
	 */
	private $validators = array();



	public function __construct(Nette\DI\Container $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
	}



	/**
	 * {@inheritDoc}
	 */
	public function getInstance(Constraint $constraint)
	{
		$className = $constraint->validatedBy();

		// Workaround for https://github.com/symfony/symfony/pull/16166.
		if ($className === 'validator.expression') {
			$className = 'Symfony\Component\Validator\Constraints\ExpressionValidator';
		}

		if (!isset($this->validators[$lClassName = ltrim(strtolower($className), '\\')])) {
			if (!$validator = $this->serviceLocator->getByType($className, FALSE)) {
				$validator = $this->serviceLocator->createInstance($className);
			}

			$this->validators[$lClassName] = $validator;
		}

		return $this->validators[$lClassName];
	}

}
