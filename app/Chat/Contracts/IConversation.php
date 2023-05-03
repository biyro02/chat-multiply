<?php

namespace App\Chat\Contracts;

use App\Http\Models\NusalUser;
use App\UserCapabilities\Models\PairProduct;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

interface IConversation
{

    const STATUS_NEW = 'new';
    const STATUS_BOT = 'bot';
    const STATUS_WAITING_DISPATCH = 'waiting_dispatch';
    const STATUS_DISPATCHED = 'dispatched';
    const STATUS_ENDED = 'ended';

    const END_CAUSE_CUSTOMER_LIMIT_EXCEEDED = 'customer_limit_exceeded';
    const END_CAUSE_AGENT_MANUAL_END = 'agent_manual_stop';
    const END_CAUSE_CUSTOMER_NOT_FOUND = 'customer_not_found';
    const END_CAUSE_INTERNAL_ERROR = 'internal_error';

    public function getConversationIdentifier();
    public function setConversationIdentifier($conversationIdentifier);

    public function getContactIdentifier();
    public function setContactIdentifier($identifier);

    public function getProductId();
    public function setProductId($productId);

    /**
     * @return mixed | PairProduct
     */
    public function getProduct();

    /**
     * @return mixed
     * Create Ticket If Not Created
     */
    public function createTicket();

    public function setChannel($channel);
    public function getChannel();

    public function setTicketId($ticketId);
    public function getTicketId();

    /**
     * @return mixed
     */
    public function getKey();

    /**
     * @return boolean
     */
    public function haveRelatedTicket();

    public function setDispatched();
    public function isDispatched() : bool;


    public function touchCustomerLastMessagedAt();
    public function touchAgentLastMessagedAt();

    /**
     * @param $status
     * @return mixed | $this
     */
    public function setStatus($status);
    public function getStatus();

    public function getChannelData();
    public function setChannelData(array $channelData);
    public function pushChannelData(array $channelData);

    public function getAdditionalData();
    public function setAdditionalData(array $additionalData);

    /**
     * @param array $additionalData
     * @return mixed
     */
    public function pushAdditionalData(array $additionalData);

    public function getContactData();
    public function setContactData(array $contactData);

    public function messages() : HasMany;
    public function product() : BelongsTo;
    public function user() : BelongsTo;
    public function ticket() : BelongsTo;

    public function isExists();

    public function fetch();

    /**
     * @return mixed | Collection
     */
    public function getMessages();

    public function getUserId();
    public function setUserId($userId);

    /**
     * @return mixed | NusalUser
     */
    public function getUser();
    public function hasUser();

    public function save();

    /**
     * @return mixed | Carbon
     */
    public function getStartDate();

    /**
     * @param $type
     * @return mixed
     */
    public function setRelationType($type);

    /**
     * @return mixed
     */
    public function deleteAlerts();

    /**
     * @return mixed
     */
    public function delete();
}
