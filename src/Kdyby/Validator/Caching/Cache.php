<?php

namespace Kdyby\Validator\Caching;

use Nette;
use Nette\Caching\Cache AS NCache;
use Nette\Object;
use Symfony\Component\Validator\Mapping\Cache\CacheInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @author Michael Moravec
 * @author Jáchym Toušek 
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
		return isset($this->cache[$class]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function read($class)
	{
		return $this->has($class) ? $this->cache[$class] : FALSE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function write(ClassMetadata $metadata)
	{
		$this->cache[$metadata->getClassName()] = $metadata;
	}

}
