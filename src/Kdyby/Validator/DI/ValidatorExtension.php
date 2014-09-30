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
use Kdyby\Translation\DI\ITranslationProvider;
use Nette;
use Nette\DI\Compiler;
use Nette\Utils\Validators;



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
class ValidatorExtension extends Nette\DI\CompilerExtension implements ITranslationProvider
{

	const TAG_LOADER = 'kdyby.validator.loader';
	const TAG_INITIALIZER = 'kdyby.validator.initializer';

	/**
	 * @var array
	 */
	public $defaults = array(
		'cache' => 'Kdyby\Validator\Caching\Cache',
		'translationDomain' => NULL,
	);



	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$builder->addDefinition($this->prefix('loader'))
			->setClass('Symfony\Component\Validator\Mapping\Loader\LoaderInterface')
			->setFactory('Symfony\Component\Validator\Mapping\Loader\LoaderChain');

		$builder->addDefinition($this->prefix('annotationsLoader'))
			->setFactory('Symfony\Component\Validator\Mapping\Loader\AnnotationLoader')
			->addTag(self::TAG_LOADER);

		$cacheFactory = self::filterArgs($config['cache']);
		if (!class_exists($cacheFactory[0]->entity) || !in_array('Symfony\Component\Validator\Mapping\Cache\CacheInterface', class_implements($cacheFactory[0]->entity), TRUE)) {
			throw new Nette\Utils\AssertionException(
				'Expected implementation of Symfony\Component\Validator\Mapping\Cache\CacheInterface, ' .
				'but ' . $cacheFactory[0]->entity  . ' given.'
			);
		}
		$builder->addDefinition($this->prefix('cache'))
			->setClass('Symfony\Component\Validator\Mapping\Cache\CacheInterface')
			->setFactory($cacheFactory[0]->entity, $cacheFactory[0]->arguments);

		$builder->addDefinition($this->prefix('metadataFactory'))
			->setClass('Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface')
			->setFactory('Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory')
			->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('constraintValidatorFactory'))
			->setClass('Symfony\Component\Validator\ConstraintValidatorFactoryInterface')
			->setFactory('Kdyby\Validator\ConstraintValidatorFactory');

		$builder->addDefinition($this->prefix('contextFactory'))
			->setClass('Symfony\Component\Validator\Context\ExecutionContextFactoryInterface')
			->setFactory('Symfony\Component\Validator\Context\ExecutionContextFactory', array('translationDomain' => $config['translationDomain']));

		$builder->addDefinition($this->prefix('validator'))
			->setClass('Symfony\Component\Validator\Validator\ValidatorInterface')
			->setFactory('Symfony\Component\Validator\Validator\RecursiveValidator');
	}



	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		$loaders = array();
		foreach (array_keys($builder->findByTag(self::TAG_LOADER)) as $service) {
			$builder->getDefinition($service)
				->setAutowired(FALSE);
			$loaders[] = '@' . $service;
		}
		$builder->getDefinition($this->prefix('loader'))
			->setArguments(array($loaders));

		$initializers = array();
		foreach (array_keys($builder->findByTag(self::TAG_INITIALIZER)) as $service) {
			$initializers[] = '@' . $service;
		}
		$builder->getDefinition($this->prefix('validator'))
			->setArguments(array(
				'metadataFactory' => $this->prefix('@metadataFactory'),
				'objectInitializers' => $initializers,
			));
	}



	/**
	 * Return array of directories, that contain resources for translator.
	 *
	 * @return string[]
	 */
	public function getTranslationResources()
	{
		$validatorClass = new \ReflectionClass('Symfony\Component\Validator\Validator');

		return array(
			dirname($validatorClass->getFileName()) . '/Resources/translations',
		);
	}



	/**
	 * @param string|\stdClass $statement
	 * @return Nette\DI\Statement[]
	 */
	private static function filterArgs($statement)
	{
		return Nette\DI\Compiler::filterArguments(array(is_string($statement) ? new Nette\DI\Statement($statement) : $statement));
	}



	public static function register(Nette\Configurator $config)
	{
		$config->onCompile[] = function ($config, Compiler $compiler) {
			$compiler->addExtension('validator', new ValidatorExtension());
		};
	}

}
