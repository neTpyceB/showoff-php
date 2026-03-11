<?php

declare(strict_types=1);

namespace App\Messaging;

use App\Messaging\Message\ContactSubmissionStoredMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;

final readonly class ContactSubmissionEventConsumer implements ContactSubmissionConsumer
{
    public function __construct(
        private ContactSubmissionEventHandler $handler,
        private string $host,
        private int $port,
        private string $user,
        private string $password,
        private string $vhost,
        private string $queueName,
    ) {}

    public function consume(int $limit): int
    {
        $connection = new AMQPStreamConnection(
            host: $this->host,
            port: $this->port,
            user: $this->user,
            password: $this->password,
            vhost: $this->vhost,
        );

        $processed = 0;

        try {
            $channel = $connection->channel();
            $channel->queue_declare($this->queueName, false, true, false, false);

            while ($processed < $limit) {
                $message = $channel->basic_get($this->queueName);
                if ($message === null) {
                    break;
                }

                $this->handler->handle(ContactSubmissionStoredMessage::fromJson($message->getBody()));

                $channel->basic_ack($message->getDeliveryTag());
                $processed++;
            }

            $channel->close();
        } finally {
            $connection->close();
        }

        return $processed;
    }
}
