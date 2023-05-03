<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/api')->middleware('api')->group(function(){

    Route::middleware('auth:api')->get('/users/~/chats', '\App\Chat\Controllers\ChatController@getAgentConversations');

    Route::prefix('/chat')->group(function(){
        /** Slack app create callback */
        Route::get('/slack/callback', '\App\Chat\Controllers\ChatController@appCreateWebhook');

        /** All chat providers webhook */
        Route::post('/webhook', '\App\Chat\Controllers\ChatController@webhook');

        /** Processes about conversation */
        Route::middleware('auth:api')->group(function(){

            Route::middleware('chat.agent')->group(function(){
                /** http requests on conversation */
                Route::get('/{conversation}', '\App\Chat\Controllers\ChatController@getConversation');
                Route::delete('/{conversation}', '\App\Chat\Controllers\ChatController@endConversation');

                Route::get('/{conversation}/messages', '\App\Chat\Controllers\ChatController@getConversationMessages');
                Route::post('/{conversation}/messages', '\App\Chat\Controllers\ChatController@sendConversationMessage');

                /** conversation related ticket */
                Route::post('/{conversation}/tickets/{ticketId}', '\App\Chat\Controllers\ChatController@relateTicket');
                Route::delete('/{conversation}/tickets/{ticketId}', '\App\Chat\Controllers\ChatController@deleteTicketRelation');
            });
        });
    });
});
