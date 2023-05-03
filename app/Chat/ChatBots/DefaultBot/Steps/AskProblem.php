<?php

namespace App\Chat\ChatBots\DefaultBot\Steps;

use App\Chat\ChatBots\BaseChatBotStep;
use App\Chat\Contracts\IMessage;

class AskProblem extends BaseChatBotStep
{
    /**
     * @param IMessage $message
     */
    public function handle(IMessage $message)
    {
        $conversation = $this->getConversation();
        $contactData = $conversation->getContactData();

        if ($contactData) {
            $findAndReplace = [
                '{product_name}' => $this->getProduct()->getName(),
                '{customer_first_name}' => $contactData['firstName'],
                '{customer_last_name}' => $contactData['lastName'],
            ];

            $replyText = 'Welcome {customer_first_name} {customer_last_name} to {product_name} Customer Care, I am your Virtual Assistant. In a few words, tell me how can I help you today?';

            $this->getConversation()->pushAdditionalData([
                'customer_name' => $contactData['firstName'] .' '. $contactData['lastName']
            ])->save();

        } else {
            $findAndReplace = [
                '{product_name}' => $this->getProduct()->getName()
            ];

            $replyText = 'Welcome to {product_name} Customer Care, I am your Virtual Assistant. In a few words, tell me how can I help you today?';
        }

        $replyText = str_replace(array_keys($findAndReplace), array_values($findAndReplace), $replyText);

        $this->getBot()->replyText($replyText);
    }

    /**
     * @param IMessage $message
     * @return string|null
     */
    public function callback(IMessage $message) : ?string
    {
        $conversation = $this->getConversation();
        $contactData = $conversation->getContactData();

        $conversation->pushAdditionalData([
            'problem' => $message->getMessage()
        ])->save();

        if ($contactData) {
            return TransferToAgent::class;
        }

        return AskName::class;
    }
}
