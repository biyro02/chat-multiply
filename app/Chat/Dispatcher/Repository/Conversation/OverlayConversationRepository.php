<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/9/21
 * Time: 9:28 AM
 */

namespace App\Chat\Dispatcher\Repository\Conversation;

use App\Chat\Models\Conversation;
use Illuminate\Support\Collection;

class OverlayConversationRepository implements IConversationRepository
{
    /**
     * @return mixed | Collection
     */
    public function allWaitingDispatch()
    {
        return Conversation::waitingDispatch()->get();
    }
}
