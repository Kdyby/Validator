Documentation
=============

This extension is here to integrate [Symfony/Validator](https://github.com/Symfony/Validator) to [Nette](https://github.com/Nette/Nette).


Installation
-----------

The best way to install Kdyby/Validator is using [Composer](http://getcomposer.org/). It is recommended to install [Kdyby/Annotations](https://github.com/Kdyby/Annotations) as well.

```sh
$ composer require kdyby/annotations
$ composer require kdyby/validator
```

Now you need to register Kdyby/Validator, [Kdyby/Annotations](https://github.com/Kdyby/Annotations)
and [Kdyby/Translation](https://github.com/Kdyby/Translation) extensions using your [neon](http://ne-on.org/) config file.

```yml
extensions:
	annotations: Kdyby\Annotations\DI\AnnotationsExtension
	translation: Kdyby\Translation\DI\TranslationExtension
	validator: Kdyby\Validator\DI\ValidatorExtension
```

See also the documentation of [Kdyby/Annotations](https://github.com/Kdyby/Annotations/blob/master/docs/en/index.md),
[Kdyby/Translation](https://github.com/Kdyby/Translation/blob/master/docs/en/index.md) and [Symfony/Validator](http://symfony.com/doc/current/book/validation.html).
