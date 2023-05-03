<?php

namespace App\Chat\ChatBots\DefaultBot\Steps;

use App\Chat\ChatBots\BaseChatBotStep;
use App\Chat\Contracts\IMessage;

class AskEmail extends BaseChatBotStep
{
    /**
     * @param IMessage $message
     */
    public function handle(IMessage $message)
    {
        $conversation = $this->getConversation();
        $customerName = $conversation->getAdditionalData()['customer_name'];

        $replyText = 'Thank you {customer_name}. Please provide your email address.';
        $replyText = str_replace('{customer_name}', $customerName, $replyText);

        $this->getBot()->replyText($replyText);
    }

    /**
     * @param IMessage $message
     * @return string|null
     */
    public function callback(IMessage $message): ?string
    {
        $mailAddress = $message->getMessage();
        $conversation = $this->getConversation();

        $cwContact = $this->getChannel()->fetchConnectWiseContact($conversation, $mailAddress);

        $conversation->save();

        if ($cwContact) {
            return TransferToAgent::class;
        }

        return EndConversation::class;
    }
}
