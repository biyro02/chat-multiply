<?php

namespace App\Chat\ChatBots\DefaultBot;

use App\Chat\ChatBots\BaseChatBot;
use App\Chat\ChatBots\DefaultBot\Steps\AskProblem;

class DefaultBot extends BaseChatBot
{
    public function getEntryStepClassName()
    {
        return AskProblem::class;
    }
}
