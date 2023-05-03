<?php

namespace App\Chat\Middleware;

use App\Chat\Exceptions\InvalidAgentException;
use App\Chat\Models\Conversation;
use App\Http\Models\NusalUser;
use Closure;

class CheckAgentHasChat
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws InvalidAgentException
     */
    public function handle($request, Closure $next)
    {
        /**
         * @var $user NusalUser
         */
        $user = $request->user();

        /**
         * @var $conversation Conversation
         */
        $conversation = $request->route('conversation');

        if ($user->is($conversation->getUser())) {
            return $next($request);
        }

        throw new InvalidAgentException();
    }
}
