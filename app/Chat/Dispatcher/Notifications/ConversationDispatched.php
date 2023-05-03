<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/9/21
 * Time: 1:23 PM
 */

namespace App\Chat\Dispatcher\Notifications;


use App\Chat\Contracts\IConversation;
use App\Chat\Dispatcher\Models\ConversationDispatchAbleUser;

class ConversationDispatched
{

    /**
     * @var IConversation|null
     */
    private $conversation = null;

    /**
     * @var ConversationDispatchAbleUser|null
     */
    private $user = null;

    /**
     * ConversationDispatched constructor.
     * @param IConversation $conversation
     * @param ConversationDispatchAbleUser $user
     */
    public function __construct(IConversation $conversation, ConversationDispatchAbleUser $user)
    {
        $this->conversation = $conversation;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    public function toSlack(){

    }
}
