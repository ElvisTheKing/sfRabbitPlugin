<?php

require_once(dirname(__FILE__) . '/BaseConsumer.php');

class Consumer extends BaseConsumer {

	public function consume($msgAmount) {
		$this->target = $msgAmount;

		$this->setUpConsumer();
		
		while (count($this->ch->callbacks)) {
			$this->ch->wait();
		}
	}

	public function processMessage($msg) {
		try {
			call_user_func($this->callback, $msg->body);
			$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
			$this->consumed++;
			$this->maybeStopConsumer($msg);
		} catch (Exception $e) {
			throw $e;
		}
	}

	protected function maybeStopConsumer($msg) {
		if ($this->consumed == $this->target) {
			$msg->delivery_info['channel']->basic_cancel($msg->delivery_info['consumer_tag']);
		}
	}

	public function purge() {
		if (!empty ($this->queueOptions['name']) and $queue=$this->queueOptions['name']) {
			return $this->ch->queue_purge($queue);
		}
	}

	public function getConsumed() {
		return $this->consumed;
	}

}