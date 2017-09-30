<?php

namespace KdybyTests\ValidatorMocks;

use Symfony\Component\Validator\Constraints as Assert;

class ArticleMock
{

	/**
	 * @Assert\NotNull()
	 * @var string
	 */
	public $title;

}
