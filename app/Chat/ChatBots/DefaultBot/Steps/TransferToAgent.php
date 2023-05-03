<?php

namespace App\Chat\ChatBots\DefaultBot\Steps;

use App\Chat\ChatBots\BaseChatBotStep;
use App\Chat\Contracts\IMessage;

class TransferToAgent extends BaseChatBotStep
{
    /**
     * @param IMessage $message
     */
    public function handle(IMessage $message)
    {
        $this->getBot()->replyText('I am now transferring you to an agent.');
        $this->getBot()->dispatchConversation();
    }

    /**
     * @param IMessage $message
     * @return string|null
     */
    public function callback(IMessage $message): ?string
    {
        return null;
    }
}
