<?php

namespace KdybyTests\ValidatorMocks;

use Nette\Object;
use Symfony\Component\Validator\Constraints as Assert;

class ArticleMock extends Object
{

	/**
	 * @Assert\NotNull()
	 * @var string
	 */
	public $title;

}
