<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Validator\Caching;

use Nette;
use Nette\Caching\Cache AS NCache;
use Nette\Object;
use Symfony\Component\Validator\Mapping\Cache\CacheInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;



/**
 * @author Michael Moravec
 * @author Jáchym Toušek
 * @deprecated Use Symfony\Component\Validator\Mapping\Cache\DoctrineCache and kdyby/doctrine-cache instead.
 */
class Cache extends Object implements CacheInterface
{

	const CACHE_NS = 'Validator';

	/**
	 * @var NCache
	 */
	private $cache;



	public function __construct(Nette\Caching\IStorage $storage, $namespace = self::CACHE_NS)
	{
		$this->cache = new NCache($storage, $namespace);
	}



	/**
	 * {@inheritdoc}
	 */
	public function has($class)
	{
		return $this->cache->load($class) !== NULL;
	}



	/**
	 * {@inheritdoc}
	 */
	public function read($class)
	{
		return $this->has($class) ? $this->cache->load($class) : FALSE;
	}



	/**
	 * {@inheritdoc}
	 */
	public function write(ClassMetadata $metadata)
	{
		$this->cache->save($metadata->getClassName(), $metadata);
	}

}
