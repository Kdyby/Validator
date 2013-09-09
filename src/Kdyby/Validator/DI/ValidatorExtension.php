<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Validator\DI;

use Kdyby;
use Nette;
use Nette\DI\Compiler;
use Nette\DI\ContainerBuilder;



if (!class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
	class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
	class_alias('Nette\Config\Helpers', 'Nette\DI\Config\Helpers');
}

if (isset(Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']) || !class_exists('Nette\Configurator')) {
	unset(Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']); // fuck you
	class_alias('Nette\Config\Configurator', 'Nette\Configurator');
}

/**
 * @author Jáchym Toušek
 * @author Michael Moravec
 * @author Filip Procházka <filip@prochazka.su>
 */
class ValidatorExtension extends Nette\DI\CompilerExtension
{

	const TAG_LOADER = 'kdyby.validator.loader';

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('loader'))
			->setClass('Symfony\Component\Validator\Mapping\Loader\LoaderInterface')
			->setFactory('Kdyby\Validator\Mapping\Loader\LoaderChain');

		$builder->addDefinition($this->prefix('annotationsLoader'))
			->setFactory('Symfony\Component\Validator\Mapping\Loader\AnnotationLoader')
			->setAutowired(FALSE)
			->addTag(self::TAG_LOADER);

		$builder->addDefinition($this->prefix('metadataFactory'))
			->setClass('Symfony\Component\Validator\MetadataFactoryInterface')
			->setFactory('Symfony\Component\Validator\Mapping\ClassMetadataFactory');

		$builder->addDefinition($this->prefix('constraintValidatorFactory'))
			->setClass('Symfony\Component\Validator\ConstraintValidatorFactoryInterface')
			->setFactory('Symfony\Component\Validator\ConstraintValidatorFactory');

		$builder->addDefinition($this->prefix('validator'))
			->setClass('Symfony\Component\Validator\ValidatorInterface')
			->setFactory('Symfony\Component\Validator\Validator');
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		$loader = $builder->getDefinition($this->prefix('loader'));
		foreach (array_keys($builder->findByTag(self::TAG_LOADER)) as $service) {
			$loader->addSetup('addLoader', array('@' . $service));
		}
	}

	public static function register(Nette\Configurator $config)
	{
		$config->onCompile[] = function ($config, Compiler $compiler) {
			$compiler->addExtension('validator', new ValidatorExtension());
		};
	}

}
