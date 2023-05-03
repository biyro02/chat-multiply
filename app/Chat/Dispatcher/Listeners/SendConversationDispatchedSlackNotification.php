<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/9/21
 * Time: 1:21 PM
 */

namespace App\Chat\Dispatcher\Listeners;


use App\Chat\Dispatcher\Events\ConversationDispatched;
use App\Http\Repositories\User\IUserRepository;
use Illuminate\Support\Facades\Notification;

class SendConversationDispatchedSlackNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * @param ConversationDispatched $event
     */
    public function handle(ConversationDispatched $event)
    {
        try{
            $userRepository = app(IUserRepository::class);
            $user = $userRepository->getNotifiableUser();
            Notification::send($user, new \App\Chat\Dispatcher\Notifications\ConversationDispatched(
                $event->getConversation(),
                $event->getUser()
            ));
        }catch(\Throwable $t){

        }

    }

}
