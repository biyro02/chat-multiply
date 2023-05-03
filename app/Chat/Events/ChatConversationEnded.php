<?php

namespace App\Chat\Events;

use App\Chat\Contracts\IChatEvent;
use App\Chat\Contracts\IConversation;
use App\Chat\Contracts\IMessage;
use App\Http\Models\Node\MessageTypes;
use App\UserCapabilities\Models\PairProduct;

class ChatConversationEnded implements IChatEvent
{
    /**
     * @var IConversation
     */
    protected $conversation;

    /**
     * @var null | string
     */
    protected $cause = null;

    /**
     * ChatConversationEnded constructor.
     * @param IConversation $conversation
     * @param $cause
     */
    public function __construct(IConversation $conversation, $cause = IConversation::END_CAUSE_AGENT_MANUAL_END)
    {
        $this->conversation = $conversation;
        $this->cause = $cause;
    }

    /**
     * @return string
     */
    public function getMessageType() : string
    {
        return MessageTypes::USER_LIVE_CHAT_CONVERSATION_ENDED;
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
     * @return PairProduct
     */
    public function getProduct()
    {
        return $this->getConversation()->getProduct();
    }

    /**
     * @return null|string
     */
    public function getCause(): ?string
    {
        return $this->cause;
    }


}
