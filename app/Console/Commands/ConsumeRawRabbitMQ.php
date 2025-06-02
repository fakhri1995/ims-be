<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessRabbitMQMessage;
use Exception;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class ConsumeRawRabbitMQ extends Command
{
    protected $signature = 'rabbitmq:consume-raw';
    protected $description = 'Consume raw JSON messages from RabbitMQ and dispatch Laravel jobs';

    public function handle()
    {
        try{
            $connection = new AMQPStreamConnection(
                env('RABBITMQ_HOST', '127.0.0.1'),
            env('RABBITMQ_PORT', 5672),
            env('RABBITMQ_USER', 'guest'),
            env('RABBITMQ_PASSWORD', 'guest'));
        $channel = $connection->channel();

        $queue = env('RABBITMQ_QUEUE', 'processed_documents');
        $channel->queue_declare($queue, false, true, false, false);

        $callback = function ($msg) {
            $data = json_decode($msg->body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Invalid JSON: " . $msg->body);
                return;
            }

            // Dispatch as a regular Laravel job
            dispatch(new ProcessRabbitMQMessage($data));
        };

        $channel->basic_consume($queue, '', false, true, false, false, $callback);

        $this->info("Listening to queue [$queue]...");
        
        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
                } catch(Exception $e){
                    return $e;
                }
    }
}