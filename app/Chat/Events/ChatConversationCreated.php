<?php

namespace App\Chat\Events;

use App\Chat\Contracts\IChatEvent;
use App\Chat\Contracts\IConversation;
use App\Chat\Contracts\IMessage;
use App\Http\Models\Node\MessageTypes;

class ChatConversationCreated implements IChatEvent
{
    /**
     * @var IConversation
     */
    protected $conversation;

    /**
     * ChatConversationStarted constructor.
     * @param IConversation $conversation
     */
    public function __construct(IConversation $conversation)
    {
        $this->conversation = $conversation;
    }

    /**
     * @return string
     */
    public function getMessageType() : string
    {
        return MessageTypes::USER_LIVE_CHAT_CONVERSATION_CREATED;
    }

    /**
     * @return array
     */
    public function getMessagePayload() : array
    {
        return [
            'conversation' => $this->getConversation(),
            'message' => $this->getMessage(),
            'product' => $this->getProduct()
        ];
    }

    /**
     * @return IConversation
     */
    public function getConversation()
    {
        return $this->conversation;
    }

    /**
     * @return IMessage
     */
    public function getMessage()
    {
        return $this->getConversation()->getMessages()->latest();
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->getConversation()->getProduct();
    }
}
