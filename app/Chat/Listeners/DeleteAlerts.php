<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/13/21
 * Time: 4:30 PM
 */

namespace App\Chat\Listeners;


use App\Chat\Contracts\IChatEvent;

class DeleteAlerts
{

    /**
     * @param IChatEvent $event
     */
    public function handle(IChatEvent $event)
    {
        try {
            $event->getConversation()->deleteAlerts();
        } catch (\Exception $e) {

        }
    }

}
