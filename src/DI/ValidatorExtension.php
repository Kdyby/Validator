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
use Kdyby\DoctrineCache\DI\Helpers;
use Kdyby\Translation\DI\ITranslationProvider;
use Nette;
use Nette\DI\Compiler;
use Nette\Utils\Validators;



/**
 * @author Filip Procházka <filip@prochazka.su>
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ValidatorExtension extends Nette\DI\CompilerExtension implements ITranslationProvider
{

	const TAG_LOADER = 'kdyby.validator.loader';
	const TAG_INITIALIZER = 'kdyby.validator.initializer';
	const TAG_CONSTRAINT_VALIDATOR = 'kdyby.validator.constraintValidator';

	/**
	 * @var array
	 */
	public $defaults = [
		'cache' => 'default',
		'translationDomain' => NULL,
		'debug' => '%debugMode%',
		'strictEmail' => FALSE,
	];



	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$builder->addDefinition($this->prefix('loader'))
			->setClass('Symfony\Component\Validator\Mapping\Loader\LoaderInterface')
			->setFactory('Symfony\Component\Validator\Mapping\Loader\LoaderChain');

		$cacheService = $builder->addDefinition($this->prefix('cache'))
			->setClass('Symfony\Component\Validator\Mapping\Cache\CacheInterface');

		$cacheFactory = self::filterArgs($config['cache']);
		if (class_exists($cacheFactory[0]->getEntity()) && in_array('Symfony\Component\Validator\Mapping\Cache\CacheInterface', class_implements($cacheFactory[0]->getEntity()), TRUE)) {
			$cacheService->setFactory($cacheFactory[0]->getEntity(), $cacheFactory[0]->arguments);
		} else {
			$cacheService->setFactory('Symfony\Component\Validator\Mapping\Cache\DoctrineCache', [
				Helpers::processCache($this, $config['cache'], 'validator', $config['debug']),
			]);
		}

		$builder->addDefinition($this->prefix('metadataFactory'))
			->setClass('Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface')
			->setFactory('Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory')
			->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('constraintValidatorFactory'))
			->setClass('Symfony\Component\Validator\ConstraintValidatorFactoryInterface')
			->setFactory('Kdyby\Validator\ConstraintValidatorFactory');

		$builder->addDefinition($this->prefix('contextFactory'))
			->setClass('Symfony\Component\Validator\Context\ExecutionContextFactoryInterface')
			->setFactory('Symfony\Component\Validator\Context\ExecutionContextFactory', ['translationDomain' => $config['translationDomain']]);

		$builder->addDefinition($this->prefix('validator'))
			->setClass('Symfony\Component\Validator\Validator\ValidatorInterface')
			->setFactory('Symfony\Component\Validator\Validator\RecursiveValidator');

		Validators::assertField($config, 'strictEmail', 'boolean');

		$builder->addDefinition($this->prefix('constraint.email'))
			->setClass('Symfony\Component\Validator\Constraints\EmailValidator')
			->setArguments([
				'strict' => $config['strictEmail'],
			])
			->addTag(self::TAG_CONSTRAINT_VALIDATOR);

		$builder->addDefinition($this->prefix('constraint.expression'))
			->setClass('Symfony\Component\Validator\Constraints\ExpressionValidator')
			->addTag(self::TAG_CONSTRAINT_VALIDATOR, [
				'validator.expression', // @link https://github.com/symfony/symfony/pull/16166
			]);

		if ($this->compiler->getExtensions('Kdyby\Annotations\DI\AnnotationsExtension')) {
			$builder->addDefinition($this->prefix('annotationsLoader'))
				->setFactory('Symfony\Component\Validator\Mapping\Loader\AnnotationLoader')
				->addTag(self::TAG_LOADER);
		}
	}



	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		$loaders = [];
		foreach (array_keys($builder->findByTag(self::TAG_LOADER)) as $service) {
			$builder->getDefinition($service)
				->setAutowired(FALSE);
			$loaders[] = '@' . $service;
		}
		$builder->getDefinition($this->prefix('loader'))
			->setArguments([$loaders]);

		$initializers = [];
		foreach (array_keys($builder->findByTag(self::TAG_INITIALIZER)) as $service) {
			$initializers[] = '@' . $service;
		}
		$builder->getDefinition($this->prefix('validator'))
			->setArguments([
				'metadataFactory' => $this->prefix('@metadataFactory'),
				'objectInitializers' => $initializers,
			]);

		$validators = [];
		foreach ($builder->findByTag(self::TAG_CONSTRAINT_VALIDATOR) as $service => $attributes) {
			foreach ((array) $attributes as $name) {
				$validators[$name] = (string) $service;
			}
			$validators[(new \ReflectionClass($builder->getDefinition($service)->getClass()))->getName()] = (string) $service;
		}
		$builder->getDefinition($this->prefix('constraintValidatorFactory'))
			->setArguments([
				'validators' => $validators,
			]);
	}



	/**
	 * Return array of directories, that contain resources for translator.
	 *
	 * @return string[]
	 */
	public function getTranslationResources()
	{
		$validatorClass = new \ReflectionClass('Symfony\Component\Validator\Constraint');

		return [
			dirname($validatorClass->getFileName()) . '/Resources/translations',
		];
	}



	/**
	 * @param string|\stdClass $statement
	 * @return Nette\DI\Statement[]
	 */
	private static function filterArgs($statement)
	{
		return Compiler::filterArguments([is_string($statement) ? new Nette\DI\Statement($statement) : $statement]);
	}



	public static function register(Nette\Configurator $config)
	{
		$config->onCompile[] = function ($config, Compiler $compiler) {
			$compiler->addExtension('validator', new ValidatorExtension());
		};
	}

}
