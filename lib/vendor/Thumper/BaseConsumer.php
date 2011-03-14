<?php

require_once(dirname(__FILE__)  . '/BaseAmqp.php');

class BaseConsumer extends BaseAmqp
{
  protected $callback;
  
  public function setCallback($callback)
  {
    $this->callback = $callback;
  }
}

?>