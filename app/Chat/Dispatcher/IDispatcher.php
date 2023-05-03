<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/9/21
 * Time: 9:22 AM
 */

namespace App\Chat\Dispatcher;


use App\Chat\Contracts\IConversation;

interface IDispatcher
{

    /**
     * @param IConversation $conversation
     * @return mixed
     */
    public function dispatchSingle(IConversation $conversation);

    /**
     * @return mixed
     */
    public function dispatchBatch();

}
