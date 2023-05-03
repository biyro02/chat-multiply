<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/14/21
 * Time: 4:02 PM
 */

namespace App\Chat\Dispatcher\Listeners;


use App\Chat\Dispatcher\Events\ConversationDispatched;
use App\Http\Models\Node\MessageTypes;
use App\Http\Services\NodeService;

class PublishChatDispatchedToAgent
{

    /**,
     * @var NodeService|null
     */
    private $service = null;

    /**
     * PublishChatDispatchedToAgent constructor.
     * @param NodeService $service
     */
    public function __construct(NodeService $service)
    {
        $this->service = $service;
    }


    public function handle(ConversationDispatched $event){
        try{
            $data = [
                'conversation' => $event->getConversation()
            ];
            $this->service->publishUserMessage($event->getUser()->getUser(), MessageTypes::USER_LIVE_CHAT_CONVERSATION_DISPATCHED, $data);
        }catch (\Throwable $t){

        }
    }
}
