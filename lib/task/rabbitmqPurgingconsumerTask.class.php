<?php

class rabbitmqPurgingconsumerTask extends sfBaseTask {

	protected function configure() {
		$this->addArguments(array(
			new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'Consumer name'),
		));

		$this->addOptions(array(
			new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
			new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
			new sfCommandOption('messages', 'm', sfCommandOption::PARAMETER_OPTIONAL, 'Number of messages to consume', 1),
			new sfCommandOption('reconnect_period', 'p', sfCommandOption::PARAMETER_OPTIONAL, 'If connection fails retry after n second', 10),
		));

		$this->namespace = 'rabbitmq';
		$this->name = 'purging-consumer';
		$this->briefDescription = 'launches rabbitmq consumer that purges queue after consuming message';
		$this->detailedDescription = <<<EOF
The [rabbitmq:consumer|INFO] launches rabbitmq consumer with given name and purges queue after consuming message.
Call it with:

  [php symfony rabbitmq:consumer|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array()) {
		define('AMQP_DEBUG', (bool) sfConfig::get('app_sfRabbitPlugin_debug', 0));

		$m = $options['messages'];
		$consume = true;

		if ($m == 0 or $m < -1) {
			return;
		}

		while ($consume) {
			try {
				$consumer = sfRabbit::getConsumer($arguments['name']);
				$consumer->consume(1);
				$consumer = sfRabbit::getConsumer($arguments['name']);
				$consumer->purge();


				if ($m != -1) {
					if ($m > 1) {
						$m--;
					} else {
						$consume = false;
					}
				}
			} catch (Exception $e) {
				$this->log($e);
				error_log($e);
				sleep($options['reconnect_period']);
			}
		}
	}

}