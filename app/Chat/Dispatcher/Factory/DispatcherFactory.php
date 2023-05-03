<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/9/21
 * Time: 9:57 AM
 */

namespace App\Chat\Dispatcher\Factory;
use App\Chat\Dispatcher\DispatcherImpl;
use App\Chat\Dispatcher\IDispatcher;
use App\Chat\Dispatcher\Repository\Conversation\OverlayConversationRepository;
use App\Chat\Dispatcher\Repository\User\CapabilityBasedUserRepository;
use App\Chat\Dispatcher\Repository\User\FakeRepository;

class DispatcherFactory
{
    /**
     * @return IDispatcher
     */
    public static function make(){
        $userRepository = new CapabilityBasedUserRepository();
        $conversationRepository = new OverlayConversationRepository();
        return new DispatcherImpl($conversationRepository, $userRepository);
    }

    public static function mock(){
        $userRepository = new FakeRepository();
        $conversationRepository = new OverlayConversationRepository();
        return new DispatcherImpl($conversationRepository, $userRepository);
    }
}
