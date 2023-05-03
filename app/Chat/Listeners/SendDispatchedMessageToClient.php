<?php

namespace App\Chat\Listeners;

use App\Chat\Channels\BaseChannel;
use App\Chat\ChatBots\BaseChatBot;
use App\Chat\Dispatcher\Events\ConversationDispatched;
use App\Chat\Models\Message;

class SendDispatchedMessageToClient
{

    /**
     * @param ConversationDispatched $event
     */
    public function handle(ConversationDispatched $event)
    {
        try {

            $conversation = $event->getConversation();

            if ($conversation->isDispatched()) {

                $agent = $event->getUser();
                $problem = $conversation->getAdditionalData()['problem'];
                $clientName = $conversation->getAdditionalData()['customer_name'];

                $channel = BaseChannel::make($conversation->getChannel(), $conversation->getProduct());

                $messages = [
                    "You are now connected to ". $agent->getFullName() .".",
                    "Hello ". $clientName ."\n\n We are happy to help you.\n\n We have received your concern regarding ". $problem
                ];

                foreach ($messages as $text) {
                    $message = new Message();
                    $message->setSender(BaseChatBot::BOT_NAME);
                    $message->setMessage($text);

                    $channel->sendMessage($conversation, $message);
                    sleep(2);
                }
            }

        } catch (\Exception $e) {

        }
    }
}
