<?php

class sfRabbit {

	protected static function getConnectionParams($name) {
		$config = sfConfig::get('app_sfRabbitPlugin_connections');

		if (empty($config[$name]) or !$config = $config[$name]) {
			throw new Exception(sprintf('There is no rabbitmq connection with "%s" name in config', $name));
		}

		if (empty($config['host'])) {
			throw new Exception(sprintf('%s rabbitmq connection must have configured host', $name));
		}
		if (empty($config['user'])) {
			throw new Exception(sprintf('%s rabbitmq connection must have configured user', $name));
		}
		if (!isset($config['password'])) {
			throw new Exception(sprintf('%s rabbitmq connection must have configured password', $name));
		}

		return array(
			'host' => $config['host'],
			'port' => empty($config['port']) ? 5672 : $config['port'],
			'user' => $config['user'],
			'password' => $config['password'],
			'vhost' => isset($config['vhost']) ? $config['vhost'] : '/'
		);
	}

	public static function getProducer($name) {
		$config = sfConfig::get('app_sfRabbitPlugin_producers');

		if (empty($config[$name]) or !$config = $config[$name]) {
			throw new Exception(sprintf('There is no rabbitmq producer with "%s" name in config', $name));
		}

		$con_name = (empty($config['connection'])) ? ('default') : ($config['connection']);
		$con_params = self::getConnectionParams($con_name);

		$producer = new Producer($con_params['host'], $con_params['port'], $con_params['user'], $con_params['password'], $con_params['vhost']);

		$exchange_options = empty($config['exchange_options']) ? array() : $config['exchange_options'];
		$producer->setExchangeOptions($exchange_options);

		return $producer;
	}

	public static function getConsumer($name) {
		$config = sfConfig::get('app_sfRabbitPlugin_consumers');
		if (empty($config[$name]) or !$config = $config[$name]) {
			throw new Exception(sprintf('There is no rabbitmq consumers with "%s" name in config', $name));
		}

		$con_name = (empty($config['connection'])) ? ('default') : ($config['connection']);
		$con_params = self::getConnectionParams($con_name);

		$consumer = new Consumer($con_params['host'], $con_params['port'], $con_params['user'], $con_params['password'], $con_params['vhost']);

		$exchange_options = empty($config['exchange_options']) ? array() : $config['exchange_options'];
		$consumer->setExchangeOptions($exchange_options);

		$queue_options = empty($config['queue_options']) ? array() : $config['queue_options'];
		$consumer->setQueueOptions($queue_options);

		if (!empty($config['callback'])) {
			$consumer->setCallback(array($config['callback'], 'execute'));
		}

		return $consumer;
	}

	public static function getAnonConsumer($name) {
		$config = sfConfig::get('app_sfRabbitPlugin_anon_consumers');

		if (empty($config[$name]) or !$config = $config[$name]) {
			throw new Exception(sprintf('There is no rabbitmq anon consumers with "%s" name in config', $name));
		}

		$con_name = (empty($config['connection'])) ? ('default') : ($config['connection']);
		$con_params = self::getConnectionParams($con_name);

		$consumer = new AnonConsumer($con_params['host'], $con_params['port'], $con_params['user'], $con_params['password'], $con_params['vhost']);

		$exchange_options = empty($config['exchange_options']) ? array() : $config['exchange_options'];
		$consumer->setExchangeOptions($exchange_options);

		if (!emtpy($config['callback'])) {
			$callback = $config['callback'];
			if (!is_array($callback)) {
				$callback = array($callback, 'execute');
			}
			$consumer->setCallback($callback);
		}


		if (!emtpy($config['routing_key'])) {
			$consumer->setRoutingKey($config['routing_key']);
		}

		return $consumer;
	}

	public static function getRpcClient($name) {
		$config = sfConfig::get('app_sfRabbitPlugin_rpc_clients');

		if (empty($config[$name]) or !$config = $config[$name]) {
			throw new Exception(sprintf('There is no rabbitmq rpc client with "%s" name in config', $name));
		}

		$con_name = (empty($config['connection'])) ? ('default') : ($config['connection']);
		$con_params = self::getConnectionParams($con_name);

		$client = new RpcClient($con_params['host'], $con_params['port'], $con_params['user'], $con_params['password'], $con_params['vhost']);
		$client->initClient();

		return $client;
	}

	public static function getRpcServer($name) {
		$config = sfConfig::get('app_sfRabbitPlugin_rpc_clients');

		if (empty($config[$name]) or !$config = $config[$name]) {
			throw new Exception(sprintf('There is no rabbitmq rpc server with "%s" name in config', $name));
		}

		if (empty($config['callback'])) {
			throw new Exception(sprintf('Callback must be set for rabbitmq rpc server with "%s" name', $name));
		}

		$con_name = (empty($config['connection'])) ? ('default') : ($config['connection']);
		$con_params = self::getConnectionParams($con_name);

		$server = new RpcServer($con_params['host'], $con_params['port'], $con_params['user'], $con_params['password'], $con_params['vhost']);
		$server->initServer($name);

		$callback = $config['callback'];
		if (!is_array($callback)) {
			$callback = array($callback, 'execute');
		}
		$server->setCallback($callback);
		$server->start();

		return $server;
	}

}