<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Queue\SerializesModels;

class ChatEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private array $response;

    /**
     * Create a new event instance.
     * @param string $message
     */
    public function __construct(Request $request)
    {
        $this->response = [
            'message' => $request['message'],
            'received_id' => $request['received_id'],
            'sender_id' => auth()->id()
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->response['received_id']),
        ];
    }

    public function broadcastAs()
    {
        return 'Chat'; // App\Event\ChatEvent
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->response['message'],
            'sender_id' => $this->response['sender_id']
        ];
    }
}