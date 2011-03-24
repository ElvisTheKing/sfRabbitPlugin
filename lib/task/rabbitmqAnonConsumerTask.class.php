<?php

class rabbitmqAnonConsumerTask extends sfBaseTask {

	protected function configure() {
		$this->addArguments(array(
			new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'Consumer name'),
		));

		$this->addOptions(array(
			new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
			new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
			new sfCommandOption('messages', 'm', sfCommandOption::PARAMETER_OPTIONAL, 'Number of messages to consume', 1),
			new sfCommandOption('r_key', 'r', sfCommandOption::PARAMETER_OPTIONAL, 'Routing Key', '#'),
			new sfCommandOption('reconnect_period', 't', sfCommandOption::PARAMETER_OPTIONAL, 'If connection fails retry after n second', 10),
		));

		$this->namespace = 'rabbitmq';
		$this->name = 'anon-consumer';
		$this->briefDescription = 'launches rabbitmq anon consumer';
		$this->detailedDescription = <<<EOF
The [rabbitmq:consumer|INFO] launches rabbitmq consumer with given name and optional routing key.
Call it with:

  [php symfony rabbitmq:anon-consumer|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array()) {

		define('AMQP_DEBUG', (bool) sfConfig::get('app_sfRabbitPlugin_debug', 0));

		$m = $options['messages'];

		while (true) {
			try {
				$consumer = sfRabbit::getConsumer($arguments['name']);
				$consumer->setRoutingKey($options['r_key']);
				$consumer->consume($m);
				break;
			} catch (Exception $e) {
				if (!empty($consumer) and get_class($consumer) == 'Consumer' and $m != -1) {
					$m = $m - $consumer->getConsumed();
				}
				sleep($options['reconnect_period']);
			}
		}
	}

}
