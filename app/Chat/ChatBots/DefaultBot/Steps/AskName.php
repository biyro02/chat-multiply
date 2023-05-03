<?php

namespace App\Chat\ChatBots\DefaultBot\Steps;

use App\Chat\ChatBots\BaseChatBotStep;
use App\Chat\Contracts\IMessage;

class AskName extends BaseChatBotStep
{
    /**
     * @param IMessage $message
     */
    public function handle(IMessage $message)
    {
        $this->getBot()->replyText('Thanks for providing those details. Before we connect you with an agent, we would like to capture some information. Please provide your first and last name.');
    }

    /**
     * @param IMessage $message
     * @return string|null
     */
    public function callback(IMessage $message): ?string
    {
        $this->getConversation()->pushAdditionalData([
            'customer_name' => $message->getMessage()
        ])->save();

        return AskEmail::class;
    }
}
