<?php

namespace App\Chat\Providers;

use App\Chat\Alert\Commands\GenerateConversationAlerts;
use App\Chat\Alert\Events\ConversationAlertDeleted;
use App\Chat\Alert\Events\ConversationAlertGenerated;
use App\Chat\Alert\Listeners\PublishGroupConversationAlerts;
use App\Chat\Commands\CheckConversationsExpiration;
use App\Chat\Commands\SubscribeSmsWebHook;
use App\Chat\Converters\Commands\Test\TestConverter;
use App\Chat\Converters\Commands\Test\TestCreateTicketForChat;
use App\Chat\Dispatcher\Commands\DispatchConversations;
use App\Chat\Dispatcher\Commands\Test\TestConversationDispatchAbleUsers;
use App\Chat\Dispatcher\Commands\Test\TestConversationDispatchAbleUsersSort;
use App\Chat\Dispatcher\Commands\Test\TestDispatchCandidateConversions;
use App\Chat\Dispatcher\Commands\Test\TestDispatcher;
use App\Chat\Dispatcher\Events\ConversationDispatched;
use App\Chat\Dispatcher\Listeners\PublishChatDispatchedToAgent;
use App\Chat\Events\ChatConversationCreated;
use App\Chat\Events\ChatConversationEnded;
use App\Chat\Events\ChatConversationStarted;
use App\Chat\Events\ChatMessageReceived;
use App\Chat\Events\ChatMessageSent;
use App\Chat\Listeners\ConvertConversationToNote;
use App\Chat\Listeners\PublishMessage;
use App\Chat\Listeners\PublishConversationExpiredToClient;
use App\Chat\Listeners\SendConversationEndedMessageToClient;
use App\Chat\Listeners\SendDispatchedMessageToClient;
use App\Chat\Middleware\CheckAgentHasChat;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class ChatServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $listen = [
        ChatConversationCreated::class => [

        ],
        ChatConversationStarted::class => [

        ],
        ChatConversationEnded::class => [
            SendConversationEndedMessageToClient::class,
            PublishConversationExpiredToClient::class,
            ConvertConversationToNote::class
        ],
        ConversationDispatched::class => [
            SendDispatchedMessageToClient::class,
            PublishChatDispatchedToAgent::class
        ],
        ChatMessageReceived::class => [
            PublishMessage::class
        ],
        ChatMessageSent::class => [
            PublishMessage::class
        ],
        /**
         * Conversation Alerts
         */
        ConversationAlertGenerated::class => [
            PublishGroupConversationAlerts::class
        ],
        ConversationAlertDeleted::class => [
            PublishGroupConversationAlerts::class
        ]
    ];

    protected $routeMiddlewares = [
        'chat.agent' => CheckAgentHasChat::class
    ];

    /**
     * @return void
     */
    public function register()
    {
        $this->loadRoutesFrom(realpath(__DIR__ .'/../routes.php'));
    }

    /**
     * @param Router $router
     */
    public function boot(Router $router)
    {
        $this->registerListeners();
        $this->registerRouteMiddlewares($router);

        if ($this->app->runningInConsole()) {

            /**
             * Register commands
             */
            $this->commands([
                CheckConversationsExpiration::class,
                GenerateConversationAlerts::class,
                DispatchConversations::class,
                TestDispatcher::class,
                TestDispatchCandidateConversions::class,
                TestConversationDispatchAbleUsersSort::class,
                TestConversationDispatchAbleUsers::class,
                TestConverter::class,
                TestCreateTicketForChat::class,
                SubscribeSmsWebHook::class
            ]);

            /**
             * Register schedules
             */
            $this->callAfterResolving(Schedule::class, function(Schedule $schedule){

                $schedule->command('conversation:dispatcher')
                    ->everyMinute()
                    ->withoutOverlapping()
                    ->appendOutputTo(storage_path('logs/conversationDispatcher.log'))
                    ->runInBackground();

                $schedule->command('conversation:generateConversationAlerts')
                    ->everyMinute()
                    ->withoutOverlapping()
                    ->appendOutputTo(storage_path('logs/conversationAlertGenerator.log'))
                    ->runInBackground();

                $schedule->command('conversation:checkExpiration')
                    ->everyMinute()
                    ->runInBackground()
                    ->withoutOverlapping()
                    ->appendOutputTo(storage_path('logs/conversationExpirationCheck.log'));

            });
        }
    }

    protected function registerListeners(){
        /**
         * Register package event listeners
         */
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    /**
     * @param Router $router
     */
    protected function registerRouteMiddlewares(Router $router){
        foreach ($this->routeMiddlewares as $name => $middleware) {
            $router->aliasMiddleware($name, $middleware);
        }
    }
}
