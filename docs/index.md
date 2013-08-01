Documentation
=============

This extension is here to integrate [Symfony/Validator](https://github.com/Symfony/Validator) to [Nette](https://github.com/Nette/Nette).


Instalation
-----------

The best way to install Arachne/Validation is using [Composer](http://getcomposer.org/):

```sh
$ composer require arachne/validation:@dev
```

Now you need to register Arachne/Validation, [Kdyby/Annotations](https://github.com/Kdyby/Annotations) and [Kdyby/Translation](https://github.com/Kdyby/Translation) extensions using your [neon](http://ne-on.org/) config file.

```yml
extensions:
	kdyby.annotations: Kdyby\Annotations\DI\AnnotationsExtension
	kdyby.translation: Kdyby\Translation\DI\TranslationExtension
	arachne.validation: Arachne\Validation\DI\ValidationExtension
```

See also the documentation of [Kdyby/Annotations](https://github.com/Kdyby/Annotations/blob/master/docs/en/index.md), [Kdyby/Translation](https://github.com/Kdyby/Translation/blob/master/docs/en/index.md) and [Symfony/Validator](http://symfony.com/doc/current/book/validation.html).
