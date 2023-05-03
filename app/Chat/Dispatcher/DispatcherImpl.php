<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/9/21
 * Time: 9:26 AM
 */

namespace App\Chat\Dispatcher;
use App\Chat\Contracts\IConversation;
use App\Chat\Dispatcher\Collections\ConversationDispatchAbleUsersCollection;
use App\Chat\Dispatcher\Events\ConversationDispatched;
use App\Chat\Dispatcher\Models\ConversationDispatchAbleUser;
use App\Chat\Dispatcher\Repository\Conversation\IConversationRepository;
use App\Chat\Dispatcher\Repository\User\IUserRepository;
use App\Chat\Dispatcher\Response\DispatchResponse;

class DispatcherImpl implements IDispatcher
{

    /**
     * @var null | IUserRepository
     */
    private $userRepository = null;

    /**
     * @var null | IConversationRepository
     */
    private $conversationRepository = null;

    /**
     * DispatcherImpl constructor.
     * @param IConversationRepository $conversationRepository
     * @param IUserRepository $userRepository
     */
    public function __construct(IConversationRepository $conversationRepository, IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->conversationRepository = $conversationRepository;
    }

    /**
     * @param IConversation $conversation
     * @return mixed
     */
    public function dispatchSingle(IConversation $conversation)
    {
        $users = $this->getUsers($conversation);
        $response = new DispatchResponse(null);

        if($users->isAnyUserAvailable()){

            $user = $users->getMostAvailableUser();
            echo "Founded User " . $user->getFullName() . "\n";

            /**
             * Update Conversation User And Status
             */
            $conversation->setUserId($user->getId());
            $conversation->setStatus(IConversation::STATUS_DISPATCHED);

            /**
             * Update agent last messaged at eliminate time problems
             */
            $conversation->touchCustomerLastMessagedAt();

            /**
             * Update Agent Last Answered
             */
            $conversation->touchAgentLastMessagedAt();
            /**
             * Save
             */
            $conversation->save();
            /**
             * User Status Change
             */
            $user->changeStatusToLiveChat();
            /**
             * Fire Event
             */
            $this->fireChatDispatched($conversation, $user);
            /**
             * Set Response User
             */
            $response->setUser($user);
        }else{
            echo "There is no chat dispatchAble users\n";
        }

        return $response;
    }

    /**
     * @return mixed
     */
    public function dispatchBatch()
    {
        $conversations = $this->getConversations();
        /**
         * @var $conversation IConversation
         */
        $responses = collect([]);
        foreach($conversations as $conversation){
            try{
                echo "Conversation With Id : " . $conversation->getKey() . " is dispatching\n";
                $response = $this->dispatchSingle($conversation);
                $responses->add($response);
            }catch (\Throwable $t){

            }
        }
        return $responses;
    }

    /**
     * @param IConversation $conversation
     * @return ConversationDispatchAbleUsersCollection
     */
    protected function getUsers(IConversation $conversation){
        /**
         * @var $users ConversationDispatchAbleUsersCollection
         */
        $users = $this->userRepository->availableUsers($conversation);
        return $users->sortByChatAvailability();
    }

    /**
     * @return mixed
     */
    protected function getConversations(){
        return $this->conversationRepository->allWaitingDispatch();
    }

    /**
     * @param IConversation $conversation
     * @param ConversationDispatchAbleUser $user
     */
    protected function fireChatDispatched(IConversation $conversation, ConversationDispatchAbleUser $user){
        event(new ConversationDispatched($conversation, $user));
    }

}
