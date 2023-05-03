<?php

namespace App\Chat\ChatBots;

use App\Chat\Contracts\IChannel;
use App\Chat\Contracts\IChatBot;
use App\Chat\Contracts\IChatBotStep;
use App\Chat\Contracts\IConversation;
use App\UserCapabilities\Models\PairProduct;

abstract class BaseChatBotStep implements IChatBotStep
{
    /**
     * @var IChatBot
     */
    protected $bot;

    /**
     * BaseStep constructor.
     * @param IChatBot $bot
     */
    public function __construct(IChatBot $bot)
    {
        $this->bot = $bot;
    }

    /**
     * @return IChatBot
     */
    protected function getBot()
    {
        return $this->bot;
    }

    /**
     * @return IConversation
     */
    protected function getConversation()
    {
        return $this->getBot()->getConversation();
    }

    /**
     * @return IChannel
     */
    protected function getChannel()
    {
        return $this->getBot()->getChannel();
    }

    /**
     * @return PairProduct
     */
    protected function getProduct()
    {
        return $this->getBot()->getProduct();
    }
}
