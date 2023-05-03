<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/9/21
 * Time: 9:27 AM
 */

namespace App\Chat\Dispatcher\Repository\Conversation;
use Illuminate\Support\Collection;

interface IConversationRepository
{
    /**
     * @return mixed | Collection
     */
    public function allWaitingDispatch();
}
