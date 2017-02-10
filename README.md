# minotaur

This is a library that helps you write web applications in PHP. It's a fork of Labrys, which was written in Hack.

[![Packagist](https://img.shields.io/packagist/v/appertly/minotaur.svg)](https://packagist.org/packages/appertly/minotaur)
[![Build Status](https://travis-ci.org/appertly/minotaur.svg)](https://travis-ci.org/appertly/minotaur)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/appertly/minotaur/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/appertly/minotaur/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/appertly/minotaur/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/appertly/minotaur/?branch=master)

## Installation

You can install this library using Composer:

```console
$ composer require appertly/minotaur
```

* The master branch (version 0.x) of this project requires PHP 7.1 and has a few dependencies.

## Compliance

Releases of this library will conform to [Semantic Versioning](http://semver.org).

Our code is intended to comply with [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/), and [PSR-4](http://www.php-fig.org/psr/psr-4/). If you find any issues related to standards compliance, please send a pull request!

## The Big Idea

Really, Minotaur is the glue between several micro libraries.

In addition to several helper classes, the Big Deal here is a mechanism to declare modules.

The `Minotaur\System` class has three dependency containers: one for configuration properties, one for *back-end* objects and one for *front-end* objects. Modules can register objects in these containers.

More details coming soon! In the meantime, please browse the code.
