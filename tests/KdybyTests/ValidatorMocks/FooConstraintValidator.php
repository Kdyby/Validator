<?php

namespace KdybyTests\ValidatorMocks;

use Nette\DI\Container;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class FooConstraintValidator extends ConstraintValidator
{

	public function __construct(Container $container)
	{
	}

	public function validate($value, Constraint $constraint)
	{
	}

}
