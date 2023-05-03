<?php

namespace App\Chat\ChatBots\DefaultBot\Steps;

use App\Chat\ChatBots\BaseChatBotStep;
use App\Chat\Contracts\IConversation;
use App\Chat\Contracts\IMessage;

class EndConversation extends BaseChatBotStep
{
    /**
     * @param IMessage $message
     */
    public function handle(IMessage $message)
    {
        $this->getBot()->replyText('Thanks for providing those details. But we apologize for any inconvenience that you do not seem as a customer that we serve you. For further information, please reach out via email at -customercare@numsp.com or via a call at +1-610-999-9999 (1000)');
        $this->getChannel()->endConversation($this->getConversation(), IConversation::END_CAUSE_CUSTOMER_NOT_FOUND);
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
