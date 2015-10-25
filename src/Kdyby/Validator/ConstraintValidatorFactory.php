<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Validator;

use Nette;
use Nette\DI\Container;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;



/**
 * @author Filip Procházka <filip@prochazka.su>
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ConstraintValidatorFactory extends Nette\Object implements ConstraintValidatorFactoryInterface
{

	/**
	 * @var Container
	 */
	private $serviceLocator;

	/**
	 * @var array
	 */
	private $validators;



	public function __construct(array $validators, Container $serviceLocator)
	{
		$this->validators = $validators;
		$this->serviceLocator = $serviceLocator;
	}



	/**
	 * {@inheritDoc}
	 */
	public function getInstance(Constraint $constraint)
	{
		$name = $constraint->validatedBy();

		if (!isset($this->validators[$name])) {
			$this->validators[$name] = new $name();
		} elseif (is_string($this->validators[$name])) {
			$this->validators[$name] = $this->serviceLocator->getService($this->validators[$name]);
		}

		return $this->validators[$name];
	}

}
