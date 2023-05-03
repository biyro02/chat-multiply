<?php

namespace App\Chat\Contracts;

interface IChatBotStep
{
    public function __construct(IChatBot $bot);

    public function handle(IMessage $message);

    public function callback(IMessage $message) : ?string;
}
