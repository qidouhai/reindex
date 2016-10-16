<?php

/**
 * @file TaskQueue.php
 * @brief This file contains the TaskQueue class.
 * @details
 * @author Filippo F. Fadda
 */


namespace ReIndex\Queue;


use ReIndex\Task\ITask;

use EoC\Exception\ClientErrorException;

use AMQPChannel;
use AMQPExchange;
use AMQPQueue;
use AMQPEnvelope;

use Phalcon\Config;
use Phalcon\Di;


/**
 * @brief A special queue to handle tasks.
 */
class TaskQueue extends AbstractQueue {

  const ROUTING_KEY = 'task_queue';

  protected $channel;
  protected $queue;


  /**
   * @copydoc AbstractQueue::__construct
   */
  public function __construct(Config $config, Di $di) {
    parent::__construct($config, $di);

    // Creates the channel.
    $this->channel = new AMQPChannel($this->amqp);

    // It doesn't prefetch messages, since the tasks can be time consuming we want execute them sequentially.
    $this->channel->qos(0, 1);

    // Declares the queue.
    $this->queue = new AMQPQueue($this->channel);
    $this->queue->setName(static::ROUTING_KEY);
    $this->queue->setFlags(AMQP_DURABLE);
    $this->queue->declareQueue();
  }


  public function __destruct() {
    parent::__destruct();
  }


  /**
   * @brief Adds a task to the queue.
   * @param[in] ITask $task A task.
   */
  public function add(ITask $task) {
    // The exchange is used to publish a message on the queue.
    $exchange = new AMQPExchange($this->channel);
    $exchange->publish(serialize($task), static::ROUTING_KEY);
  }


  /**
   * @brief Performs the execution of the next task in the queue.
   */
  public function perform() {
    $jobsCounter = $this->config['application']['maxJobs'];

    $callback = function(AMQPEnvelope $msg, AMQPQueue $queue) use (&$jobsCounter) {
      try {
        // Creates a new task.
        $task = unserialize($msg->getBody());

        // Executes the task.
        $task->execute();

        // Acknowledges the receipt of the message.
        $queue->ack($msg->getDeliveryTag());
      }
      catch (ClientErrorException $e) {
        // Just in case the document doesn't exist we acknowledge the message,
        // since we don't have to execute the related task anymore.
        if ($e->getResponse()->getStatusCode() == 404) {
          $queue->ack($msg->getDeliveryTag());
          $this->log->warning($e);
        }
      }
      catch (\Exception $e) {
        $this->log->error($e);
        $queue->nack($msg->getDeliveryTag(), AMQP_REQUEUE);
      }

      // To avoid long running processes and consequently memory leaks,
      // the worker consumes at most N messages, then exit.
      if ($jobsCounter > 1) {
        $jobsCounter--;

        // Returns `true` to consume the next message.
        return TRUE;
      }
      else {
        // Forces the consume() method to exit.
        return FALSE;
      }

    };

    $this->queue->consume($callback);
  }

}