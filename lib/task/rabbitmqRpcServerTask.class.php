<?php

class rabbitmqRpcServerTask extends sfBaseTask {

	protected function configure() {
		$this->addArguments(array(
			new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'Rpc server name'),
		));

		$this->addOptions(array(
			new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
			new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
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
		define('AMQP_DEBUG', $options['env'] == 'dev');

		$server = sfRabbit::getRpcServer($arguments['name']);
		$server->start();
	}

}
