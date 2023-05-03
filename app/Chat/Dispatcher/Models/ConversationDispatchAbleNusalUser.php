<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/9/21
 * Time: 9:33 AM
 */

namespace App\Chat\Dispatcher\Models;


use App\Http\Models\NusalUser;
use App\Http\Models\Status;

class ConversationDispatchAbleNusalUser extends ConversationDispatchAbleUser
{

    /**
     * ConversationDispatchAbleNusalUser constructor.
     * @param NusalUser $user
     */
    public function __construct(NusalUser $user)
    {
        parent::__construct($user);
    }

    /**
     * @return mixed
     */
    public  function getFullName()
    {
        return $this->getUser()->getFullName();
    }

    /**
     * @return mixed
     */
    public  function getId()
    {
        return $this->getUser()->getKey();
    }

    /**
     * @return mixed|string
     */
    public  function getStatusString()
    {
        return $this->getUser()->getStatusString();
    }

    /**
     * @throws \App\Http\Exceptions\GroupNotFoundException
     * @throws \App\Http\Exceptions\NewStatusMustHaveWorkingTicketException
     * @throws \App\Http\Exceptions\PhoneAvailableUserLimitExceeded
     * @throws \App\Http\Exceptions\StatusNotFoundException
     */
    public  function changeStatusToLiveChat()
    {
        /**
         * @var $newStatus Status
         */
        $newStatus = Status::slug(Status::ON_LIVE_CHAT_SLUG)->first();
        return $this->getUser()->changeStatus($this->getUser()->status(), $newStatus->getKey());
    }

    /**-
     * @return array|mixed
     * @throws \App\Http\Exceptions\GroupNotFoundException
     * @throws \App\Http\Exceptions\NewStatusMustHaveWorkingTicketException
     * @throws \App\Http\Exceptions\PhoneAvailableUserLimitExceeded
     * @throws \App\Http\Exceptions\StatusNotFoundException
     */
    public function jsonSerialize()
    {
        return $this->getUser()->toArray();
    }
}
