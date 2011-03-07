<?php

class rabbitmqConsumerTask extends sfBaseTask {

	protected function configure() {
		$this->addArguments(array(
			new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'Consumer name'),
		));

		$this->addOptions(array(
			new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
			new sfCommandOption('messages', 'm', sfCommandOption::PARAMETER_OPTIONAL, 'Number of messages to consume', 1),
		));

		$this->namespace = 'rabbitmq';
		$this->name = 'consumer';
		$this->briefDescription = 'launches rabbitmq consumer';
		$this->detailedDescription = <<<EOF
The [rabbitmq:consumer|INFO] launches rabbitmq consumer with given name .
Call it with:

  [php symfony rabbitmq:consumer|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array()) {
		define('AMQP_DEBUG', $options['env']=='dev');

		$consumer=sfRabbit::getConsumer($arguments['name']);
		$consumer->consume($options['messages']);
	}

}
