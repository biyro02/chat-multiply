<?php

namespace App\Chat\Listeners;

use App\Chat\Contracts\IConversation;
use App\Chat\Events\ChatConversationEnded;
use App\Http\Models\SystemConfig;
use App\Http\Services\NodeService;

class PublishConversationExpiredToClient
{

    /**
     * @var NodeService|null
     */
    private $service = null;

    /**
     * PublishConversationExpiredToClient constructor.
     * @param NodeService $service
     */
    public function __construct(NodeService $service)
    {
        $this->service = $service;
    }

    /**
     * @param ChatConversationEnded $event
     */
    public function handle(ChatConversationEnded $event)
    {
        try {
            $user = $event->getConversation()->getUser();
            $cause = $event->getCause();

            if ($user && $cause === IConversation::END_CAUSE_CUSTOMER_LIMIT_EXCEEDED) {
                $ruleMinute = SystemConfig::getLiveChatOvertimeLimit();
                $data = [
                    'conversation' => $event->getConversation(),
                    'message' => 'Customer\'s conversation was ended after customer response limit exceed(' . $ruleMinute . ' minutes)',
                ];
                $this->service->publishUserMessage($user, $event->getMessageType(), $data);
            }
        } catch (\Exception $e) {

        }
    }
}
