<?php

namespace Enqueue\Psr\Spec;

use Enqueue\Psr\PsrContext;
use Enqueue\Psr\PsrMessage;
use Enqueue\Psr\PsrQueue;
use Enqueue\Psr\PsrTopic;
use PHPUnit\Framework\TestCase;

/**
 * @group functional
 */
abstract class SendToTopicAndReceiveNoWaitFromQueueSpec extends TestCase
{
    public function test()
    {
        $context = $this->createContext();
        $topic = $this->createTopic($context, 'send_to_topic_and_receive_from_queue_spec');
        $queue = $this->createQueue($context, 'send_to_topic_and_receive_from_queue_spec');

        $consumer = $context->createConsumer($queue);

        // guard
        $this->assertNull($consumer->receiveNoWait());

        $expectedBody = __CLASS__.time();

        $context->createProducer()->send($topic, $context->createMessage($expectedBody));

        $startTime = microtime(true);
        $message = $consumer->receiveNoWait();

        $this->assertLessThan(2, microtime(true) - $startTime);

        $this->assertInstanceOf(PsrMessage::class, $message);
        $consumer->acknowledge($message);

        $this->assertSame($expectedBody, $message->getBody());
    }

    /**
     * @return PsrContext
     */
    abstract protected function createContext();

    /**
     * @param PsrContext $context
     * @param string     $queueName
     *
     * @return PsrQueue
     */
    protected function createQueue(PsrContext $context, $queueName)
    {
        return $context->createQueue($queueName);
    }

    /**
     * @param PsrContext $context
     * @param string     $topicName
     *
     * @return PsrTopic
     */
    protected function createTopic(PsrContext $context, $topicName)
    {
        return $context->createTopic($topicName);
    }
}
