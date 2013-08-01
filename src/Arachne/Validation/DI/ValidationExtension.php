<?php

/**
 * This file is part of the Arachne Validation extenstion
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Validation\DI;

/**
 * @author Jáchym Toušek
 */
class ValidationExtension extends \Nette\DI\CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('loader'))
				->setClass('Symfony\Component\Validator\Mapping\Loader\LoaderInterface')
				->setFactory('Symfony\Component\Validator\Mapping\Loader\AnnotationLoader');

		$builder->addDefinition($this->prefix('metadataFactory'))
				->setClass('Symfony\Component\Validator\MetadataFactoryInterface')
				->setFactory('Symfony\Component\Validator\Mapping\ClassMetadataFactory');

		$builder->addDefinition($this->prefix('validatorFactory'))
				->setClass('Symfony\Component\Validator\ConstraintValidatorFactoryInterface')
				->setFactory('Symfony\Component\Validator\ConstraintValidatorFactory');

		$builder->addDefinition($this->prefix('validator'))
				->setClass('Symfony\Component\Validator\ValidatorInterface')
				->setFactory('Symfony\Component\Validator\Validator');
	}

}
