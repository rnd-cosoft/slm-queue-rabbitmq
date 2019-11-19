SlmQueueRabbitMq
================

[![Build Status](https://travis-ci.org/rnd-cosoft/slm-queue-rabbitmq.svg?branch=master)](https://travis-ci.org/rnd-cosoft/slm-queue-rabbitmq)

Created by Cosoft RnD team

Requirements
------------
* [Zend-MVC](https://github.com/zendframework/zend-mvc)
* [SlmQueue](https://github.com/juriansluiman/SlmQueue)
* [php-amqplib](https://github.com/php-amqplib)


Installation
------------

First, install SlmQueue ([instructions here](https://github.com/juriansluiman/SlmQueue/blob/master/README.md)). Then,
install SlmQueueRabbitMQ by executing the following in your project's root directory:

```bash
composer require rnd-cosoft/slm-queue-rabbitmq
```

Then, enable the module by adding `SlmQueueRabbitMQ` in your application.config.php file.

