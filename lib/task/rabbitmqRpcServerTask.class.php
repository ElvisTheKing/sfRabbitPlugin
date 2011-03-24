<?php

class rabbitmqRpcServerTask extends sfBaseTask {

	protected function configure() {
		$this->addArguments(array(
			new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'Rpc server name'),
		));

		$this->addOptions(array(
			new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
			new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
			new sfCommandOption('reconnect_period', 'p', sfCommandOption::PARAMETER_OPTIONAL, 'If connection fails retry after n second', 10),
		));

		$this->namespace = 'rabbitmq';
		$this->name = 'rpc-server';
		$this->briefDescription = 'launches rabbitmq rpc-server';
		$this->detailedDescription = <<<EOF
The [rabbitmq:consumer|INFO] launches rabbitmq rpc-server with given name .
Call it with:

  [php symfony rabbitmq:rpc-server|INFO]
EOF;
	}

	protected function execute($arguments = array(), $options = array()) {
		define('AMQP_DEBUG', (bool) sfConfig::get('app_sfRabbitPlugin_debug', 0));


		while (true) {
			try {
				$server = sfRabbit::getRpcServer($arguments['name']);
				$server->start();
				break;
			} catch (Exception $e) {
				$this->log($e);
				error_log($e);
				sleep($options['reconnect_period']);
			}
		}
	}

}
