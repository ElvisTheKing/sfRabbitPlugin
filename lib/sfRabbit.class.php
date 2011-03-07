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

		if (!emtpy($config['callback'])) {
			$consumer->setCallback(array($config['callback'],'execute'));
		}

		return $consumer;
	}

}