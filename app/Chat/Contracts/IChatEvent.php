<?php

namespace App\Chat\Contracts;

interface IChatEvent
{
    public function getMessageType() : string;

    public function getMessagePayload() : array;

    /**
     * @return mixed | IConversation
     */
    public function getConversation();

    public function getMessage();

    public function getProduct();
}
