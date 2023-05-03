<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/9/21
 * Time: 9:30 AM
 */

namespace App\Chat\Dispatcher\Repository\User;

use App\Chat\Contracts\IConversation;
use App\Chat\Dispatcher\Collections\ConversationDispatchAbleUsersCollection;
use App\Chat\Dispatcher\Models\ConversationDispatchAbleNusalUser;
use App\Http\Models\NusalUser;
use App\UserCapabilities\Models\Capability;
use App\UserCapabilities\Models\CapabilityChannel;
use App\UserCapabilities\Models\PairProduct;
use Illuminate\Support\Collection;

class CapabilityBasedUserRepository implements IUserRepository
{

    /**
     * @var null | CapabilityChannel
     */
    protected $channel = null;

    /**
     * @return CapabilityChannel | null
     */
    protected function getChannel(){
        if(!$this->channel){
            $this->channel = CapabilityChannel::liveChat()->first();
        }
        return $this->channel;
    }

    /**
     * @param IConversation $conversation
     * @return ConversationDispatchAbleUsersCollection
     * @throws \App\Http\Exceptions\GroupNotFoundException
     * @throws \App\Http\Exceptions\NewStatusMustHaveWorkingTicketException
     * @throws \App\Http\Exceptions\PhoneAvailableUserLimitExceeded
     * @throws \App\Http\Exceptions\StatusNotFoundException
     */
    public function availableUsers(IConversation $conversation)
    {
        return $this->forProduct($conversation->getProduct());
    }

    /**
     * @param PairProduct $product
     * @return ConversationDispatchAbleUsersCollection
     * @throws \App\Http\Exceptions\GroupNotFoundException
     * @throws \App\Http\Exceptions\NewStatusMustHaveWorkingTicketException
     * @throws \App\Http\Exceptions\PhoneAvailableUserLimitExceeded
     * @throws \App\Http\Exceptions\StatusNotFoundException
     */
    protected function forProduct(PairProduct $product){

        /**
         * @var $productAndChatCapabilities Collection
         */
        $productAndChatCapabilities = $product
            ->capabilities()
            ->with('user')
            ->capabilityChannel($this->getChannel())
            ->get();


        $chatDispatchAbleUsers = new ConversationDispatchAbleUsersCollection();
        /**
         * @var $productAndChatCapabilities Capability
         */
        foreach($productAndChatCapabilities as $productAndCallCapability){
            $user = $productAndCallCapability->getUser();
            /**
             * @var $user NusalUser
             */
            if($user && $user->isAvailableForConversation()){
                $chatDispatchAbleUsers->add(new ConversationDispatchAbleNusalUser($user));
            }
        }
        return $chatDispatchAbleUsers;
    }
}
