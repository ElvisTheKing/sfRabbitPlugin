sfRabbitPlugin is symfony 1.4 plugin that provides access to rabbitmq messaging system.

Now it's just backport of videlalvaro's symfony2 [RabbitMqBundle](https://github.com/videlalvaro/RabbitMqBundle) and it uses unchanged videlalvaro's [Thumper](https://github.com/videlalvaro/Thumper) and tnc's [php-amqplib](http://github.com/tnc/php-amqplib) backend.

It uses Consumer and Producer classes from thumper ( https://github.com/videlalvaro/Thumper ), but configures them via symfony yml configuration, so checkout thumper examples too!

Some examples:

Publish message $msg with $routing_key
define producer in config
    producers:
      test:
        connection:       default
        exchange_options: {name: test_direct, type: direct}

and then publish message somewhere
try {
  $producer = sfRabbit::getProducer('test');
  $producer->publish(serialize($msg), $routing_key);
} catch (Exception $e){
  sfContext::getInstance()->getLogger()->err($e);
  }
}


Consume messages

create message handler

class MsgHandler
{
    public static function execute($msg)
    {        
        $msg = unserialize($msg);
        do_something_with_message($msg)
    }
}

define consumer in config
    consumers:
      test:
        connection:       default
        exchange_options: {name: test_direct, type: direct}
        queue_options: {name: test}
        callback:         MsgHandler 
        
==== run consumer task or check its code and use it somewhere else ====
symfony rabbitmq:consumer test 
you can find all optional arguments in source code
