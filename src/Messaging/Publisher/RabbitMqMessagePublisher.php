<?php

declare(strict_types=1);

namespace App\Messaging\Publisher;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

final readonly class RabbitMqMessagePublisher implements MessagePublisher
{
    public function __construct(
        private string $host,
        private int $port,
        private string $user,
        private string $password,
        private string $vhost,
        private string $queueName,
    ) {}

    public function publish(string $payload): void
    {
        $connection = new AMQPStreamConnection(
            host: $this->host,
            port: $this->port,
            user: $this->user,
            password: $this->password,
            vhost: $this->vhost,
        );

        try {
            $channel = $connection->channel();
            $channel->queue_declare($this->queueName, false, true, false, false);
            $channel->basic_publish(
                new AMQPMessage($payload, [
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                    'content_type' => 'application/json',
                ]),
                '',
                $this->queueName,
            );
            $channel->close();
        } finally {
            $connection->close();
        }
    }
}
