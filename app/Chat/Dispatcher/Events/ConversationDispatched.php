<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/9/21
 * Time: 10:13 AM
 */

namespace App\Chat\Dispatcher\Events;


use App\Chat\Contracts\IConversation;
use App\Chat\Dispatcher\Models\ConversationDispatchAbleUser;

class ConversationDispatched
{
    /**
     * @var IConversation|null
     */
    private $conversation = null;

    /**
     * @var null | ConversationDispatchAbleUser
     */
    private $user = null;

    /**
     * ChatDispatched constructor.
     * @param IConversation $conversation
     * @param ConversationDispatchAbleUser $user
     */
    public function __construct(IConversation $conversation, ConversationDispatchAbleUser $user)
    {
        $this->conversation = $conversation;
        $this->user = $user;
    }

    /**
     * @return IConversation|null
     */
    public function getConversation(): ?IConversation
    {
        return $this->conversation;
    }

    /**
     * @return ConversationDispatchAbleUser|null
     */
    public function getUser(): ?ConversationDispatchAbleUser
    {
        return $this->user;
    }
}
