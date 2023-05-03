<?php

namespace App\Chat\Listeners;

use App\Chat\Channels\BaseChannel;
use App\Chat\ChatBots\BaseChatBot;
use App\Chat\Contracts\IConversation;
use App\Chat\Events\ChatConversationEnded;
use App\Chat\Models\Message;

class SendConversationEndedMessageToClient
{

    /**
     * @param ChatConversationEnded $event
     */
    public function handle(ChatConversationEnded $event)
    {
        try {

            $cause = $event->getCause();
            $conversation = $event->getConversation();

            if ($conversation->getStatus() === IConversation::STATUS_ENDED) {

                $message = new Message();
                $message->setSender(BaseChatBot::BOT_NAME);
                $channel = BaseChannel::make($conversation->getChannel(), $conversation->getProduct());

                if ($cause === IConversation::END_CAUSE_AGENT_MANUAL_END) {
                    $message->setMessage("Feel free to ping us for any other issues. We will be happy to assist you.\n\nThank you for chatting. Good-bye and take care");
                } else if ($cause === IConversation::END_CAUSE_CUSTOMER_LIMIT_EXCEEDED) {
                    $message->setMessage("Your conversation is ended because of inactivity.");
                } else {
                    return true;
                }

                $channel->sendMessage($conversation, $message);
            }

        } catch (\Exception $e) {

        }
    }
}
