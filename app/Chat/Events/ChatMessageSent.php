<?php

namespace App\Chat\Events;

use App\Chat\Contracts\IChatEvent;
use App\Chat\Contracts\IConversation;
use App\Chat\Contracts\IMessage;
use App\Http\Models\Node\MessageTypes;
use App\UserCapabilities\Models\PairProduct;

class ChatMessageSent implements IChatEvent
{
    /**
     * @var IMessage
     */
    protected $message;

    /**
     * ChatMessageSended constructor.
     * @param IMessage $message
     */
    public function __construct(IMessage $message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessageType() : string
    {
        return MessageTypes::USER_LIVE_CHAT_MESSAGE_SENT;
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
     * @return IMessage
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return IConversation
     */
    public function getConversation()
    {
        return $this->getMessage()->getConversation();
    }

    /**
     * @return PairProduct
     */
    public function getProduct()
    {
        return $this->getConversation()->getProduct();
    }
}
