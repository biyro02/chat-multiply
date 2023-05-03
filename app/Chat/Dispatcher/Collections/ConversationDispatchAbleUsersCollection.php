<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/9/21
 * Time: 9:37 AM
 */

namespace App\Chat\Dispatcher\Collections;


use App\Chat\Dispatcher\Models\ConversationDispatchAbleUser;
use Illuminate\Support\Collection;

class ConversationDispatchAbleUsersCollection extends Collection
{

    /**
     * @return bool
     */
    public function isAnyUserAvailable(){
        return $this->isNotEmpty();
    }

    public function sortByChatAvailability(){
        $sortedCollection = $this->sort(function($user1, $user2){
            /**
             * @var $user1 ConversationDispatchAbleUser
             * @var $user2 ConversationDispatchAbleUser
             */
            if($user1->getRandomCallDispatchOrder() === $user2->getRandomCallDispatchOrder()){
                return 0;
            }
            return $user1->getRandomCallDispatchOrder() < $user2->getRandomCallDispatchOrder() ? -1 : 1;
        });

        return $sortedCollection;
    }

    /**
     * @return ConversationDispatchAbleUser
     */
    public function getMostAvailableUser(){
        return $this->first();
    }

}
