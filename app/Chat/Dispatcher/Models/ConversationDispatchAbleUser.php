<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/9/21
 * Time: 9:31 AM
 */

namespace App\Chat\Dispatcher\Models;


use App\Http\Models\NusalUser;

abstract class ConversationDispatchAbleUser implements \JsonSerializable
{

    /**
     * @var null | int
     */
    private $randomCallDispatchOrder = null;

    /**
     * @var null | NusalUser
     */
    private $user = null;

    /**
     * ConversationDispatchAbleNusalUser constructor.
     * @param NusalUser $user
     */
    public function __construct(NusalUser $user)
    {
        $this->user = $user;
    }


    /**
     * @return NusalUser|null
     */
    public function getUser(): ?NusalUser
    {
        return $this->user;
    }


    public abstract function getFullName();
    public abstract function getId();
    public abstract function getStatusString();

    public function getRandomCallDispatchOrder()
    {
        if($this->randomCallDispatchOrder === null){
            $this->randomCallDispatchOrder = rand(0,1000);
        }
        return $this->randomCallDispatchOrder;
    }

    /**
     * @return mixed
     */
    public abstract function changeStatusToLiveChat();
}
