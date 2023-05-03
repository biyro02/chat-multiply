<?php

namespace App\Chat\Listeners;

use App\Chat\Contracts\IChatEvent;
use App\Http\Services\NodeService;

class PublishMessage
{
    /**
     * @var NodeService|null
     */
    private $service = null;

    /**
     * PublishMessage constructor.
     * @param NodeService $service
     */
    public function __construct(NodeService $service)
    {
        $this->service = $service;
    }

    /**
     * @param IChatEvent $event
     */
    public function handle(IChatEvent $event)
    {
        try {
            if ($user = $event->getConversation()->getUser()) {
                $this->service->publishUserMessage(
                    $user,
                    $event->getMessageType(),
                    $event->getMessagePayload()
                );
            }
        } catch (\Exception $e) {

        }
    }
}
