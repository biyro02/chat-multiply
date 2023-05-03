<?php

namespace App\Chat\Contracts;

interface IChatBot
{
    public function __construct(IChannel $channel, IConversation $conversation);

    public function getChannel();
    public function getConversation();
    public function getProduct();

    public function reply(IMessage $message);
    public function replyText($text);

    public function handle(IMessage $message);

    public function dispatchConversation();
}
