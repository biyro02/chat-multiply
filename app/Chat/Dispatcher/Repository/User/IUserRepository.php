<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/9/21
 * Time: 9:29 AM
 */

namespace App\Chat\Dispatcher\Repository\User;


use App\Chat\Contracts\IConversation;
use App\Chat\Dispatcher\Repository\IConversationRepository;

interface IUserRepository
{

    public function availableUsers(IConversation $conversation);
}
