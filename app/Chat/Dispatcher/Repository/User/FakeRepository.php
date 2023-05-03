<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/9/21
 * Time: 12:35 PM
 */

namespace App\Chat\Dispatcher\Repository\User;


use App\Chat\Contracts\IConversation;
use App\Chat\Dispatcher\Collections\ConversationDispatchAbleUsersCollection;
use App\Chat\Dispatcher\Models\ConversationDispatchAbleNusalUser;
use App\Chat\Dispatcher\Models\ConversationDispatchAbleUser;
use App\Http\Models\NusalUser;

class FakeRepository implements IUserRepository
{

    /**
     * @param IConversation $conversation
     * @return mixed | ConversationDispatchAbleUsersCollection
     */
    public function availableUsers(IConversation $conversation)
    {
        $users = NusalUser::find([10268, 12556]);
        $usersCollection = new ConversationDispatchAbleUsersCollection();
        /**
         *
         */
        foreach($users as $user){
           $usersCollection->add(new ConversationDispatchAbleNusalUser($user));
        }
        return $usersCollection;
    }
}
