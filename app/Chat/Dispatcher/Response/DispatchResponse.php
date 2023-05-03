<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/9/21
 * Time: 9:25 AM
 */

namespace App\Chat\Dispatcher\Response;


use App\Chat\Dispatcher\Models\ConversationDispatchAbleUser;

class DispatchResponse
{

    /**
     * @var ConversationDispatchAbleUser|null
     */
    private $user = null;
    /**
     * @var null | boolean
     */
    private $isDispatched = false;

    /**
     * DispatchResponse constructor.
     * @param ConversationDispatchAbleUser|null $user
     */
    public function __construct(?ConversationDispatchAbleUser $user = null)
    {
        $this->user = $user;
    }

    /**
     * @param ConversationDispatchAbleUser|null $user
     */
    public function setUser(?ConversationDispatchAbleUser $user): void
    {
        if($user !== null){
            $this->isDispatched = true;
        }
        $this->user = $user;
    }

    /**
     * @return bool|null
     */
    public function isDispatched(): ?bool
    {
        return $this->isDispatched;
    }

    /**
     * @return ConversationDispatchAbleUser|null
     */
    public function getUser(): ?ConversationDispatchAbleUser
    {
        return $this->user;
    }
}
