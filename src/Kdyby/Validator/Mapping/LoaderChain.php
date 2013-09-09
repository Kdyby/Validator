<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Validator\Mapping;

use Symfony\Component\Validator\Mapping\Loader\LoaderInterface;
use Symfony\Component\Validator\Mapping\Loader\LoaderChain as BaseLoaderChain;



class LoaderChain extends BaseLoaderChain
{

	public function __construct(array $loaders = array())
	{
		parent::__construct($loaders);
	}

    public function addLoader(LoaderInterface $loader)
    {
        $this->loaders[] = $loader;
    }

}
