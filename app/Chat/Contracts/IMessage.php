<?php

namespace App\Chat\Contracts;

use App\Chat\Models\Message;
use App\Http\Models\NusalUser;
use Carbon\Carbon;
use Illuminate\Support\Collection;

interface IMessage
{
    public function conversation();

    public function getKey();
    /**
     * @return mixed | IConversation
     */
    public function getConversation();

    public function attachments();

    /**
     * @return Collection
     */
    public function getAttachments();

    /**
     * @param $message
     * @return Message
     */
    public function setMessage($message);
    public function getMessage();

    public function setRecipient($recipient);
    public function getRecipient();

    public function setSender($sender);

    /**
     * @return string
     */
    public function getSender();

    public function setTimestamp($timestamp);

    /**
     * @return mixed | Carbon
     */
    public function getTimestamp();

    public function setRaw($data);
    public function getRaw();

    public function setIncoming();
    public function isIncoming() : bool;

    public function setOutgoing();
    public function isOutgoing() : bool;

    public function getDirection();

    public function save();
}
