<?php

namespace App\Chat\Contracts;

use App\Chat\Services\BaseService;
use App\UserCapabilities\Models\PairProduct;

interface IChannel
{
    public function __construct(PairProduct $product, BaseService $service);

    public static function make($serviceName, PairProduct $product);

    public function handleIncomingMessage($payload);

    public function getChannelSlug() : string;

    public function getProduct();
    public function getService();
    public function getPayload();

    public function createConversation(IConversation $conversation);
    public function endConversation(IConversation $conversation, $cause = 'agent_manual_stop');

    public function sendMessage(IConversation $conversation, IMessage $message);
    public function addMessageToConversation(IConversation $conversation, IMessage $message);

    public function fetchConnectWiseContact(IConversation $conversation, $contactIdentifier = null);

}
