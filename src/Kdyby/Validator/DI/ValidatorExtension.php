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

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('validatorBuilder'))
			->setClass('Symfony\Component\Validator\ValidatorBuilderInterface')
			->setFactory('Symfony\Component\Validator\ValidatorBuilder')
			->addSetup('enableAnnotationMapping')
			->addSetup('setTranslator')
			->addSetup('setMetadataCache', array(
				new Nette\DI\Statement('Kdyby\Validator\Caching\Cache', array(
					'@Nette\Caching\IStorage',
					'Symfony.Validator'
				))
			));

		$builder->addDefinition($this->prefix('validator'))
			->setClass('Symfony\Component\Validator\ValidatorInterface')
			->setFactory($this->prefix('@validatorBuilder') . '::getValidator');
	}



	public static function register(Nette\Configurator $config)
	{
		$config->onCompile[] = function ($config, Compiler $compiler) {
			$compiler->addExtension('validator', new ValidatorExtension());
		};
	}

}
