<?php

namespace App\Chat\Models;

use App\Chat\Alert\Events\ConversationAlertDeleted;
use App\Chat\Contracts\IAlertAble;
use App\Chat\Contracts\IConversation;
use App\Http\Constants\TicketStatuses;
use App\Http\Models\NusalUser;
use App\Http\Services\ConnectWise\PriorityService;
use App\Http\Services\ConnectWise\SourceService;
use App\Http\Services\ConnectWise\TicketService;
use App\TicketManagement\Events\Ticket\TicketCreatedOverlay;
use App\TicketManagement\Models\ConnectWiseTicket;
use App\UserCapabilities\Models\PairProduct;
use App\UserCapabilities\Models\ProductTicketBoard;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

/**
 * Class Conversation
 * @package App\Chat\Models
 * @method Builder|Conversation userId($userId)
 */
class Conversation extends Model implements IConversation, IAlertAble
{
    use SoftDeletes;
    use HybridRelations;

    const MANUAL_RELATION_TYPE = 'manual';
    const AUTO_RELATION_TYPE = 'auto';

    public $connection = 'mysql';
    public $table = 'chat_conversations';
    public $timestamps = true;
    public $guarded = [];

    public $casts = [
        'channel_data' => 'json',
        'additional_data' => 'json',
        'contact_data' => 'json'
    ];

    protected $dates = [
        'agent_last_messaged_at',
        'customer_last_messaged_at'
    ];

    /**
     * @return bool
     */
    public function isExists()
    {
        return $this->exists;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $arr = parent::toArray();
        $arr['company_id'] = $this->getCompanyId();
        $arr['contact_id'] = $this->getContactData()['id'];
        $arr['company_name'] = $this->getCompanyName();
        return $arr;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
       return $this->getKey();
    }

    /**
     * @param $alertTypeId
     * @return mixed|void
     */
    public function deleteAlertsWithTypeId($alertTypeId)
    {
        /**
         * @var $alerts Collection
         */
        $alerts = $this
            ->alerts()
            ->alertTypeId($alertTypeId)
            ->get();

        $alerts->each(function($alert){
            /**
             * @var $alert ConversationAlert
             */
            $alert->delete();
            event(new ConversationAlertDeleted($alert));
        });
    }

    /**
     * @return mixed|void
     */
    public function deleteAlerts(){

        $this->getAlerts()->each(function($alert){
            /**
             * @var $alert ConversationAlert
             */
            $alert->delete();
            event(new ConversationAlertDeleted($alert));
        });
    }

    /**
     * @return $this
     */
    public function fetch()
    {
        if ($this->isExists() === false) {
            $check = $this->where($this->getAttributes())->first();

            if ($check) {
                $this->setRawAttributes($check->getAttributes(), true);
                $this->exists = true;
            }
        }

        return $this;
    }

    /**
     * @param $query
     * @param $conversationId
     * @return mixed
     */
    public function scopeConversationIdentifier($query, $conversationId)
    {
        return $query->where('conversation_identifier', $conversationId);
    }

    public function scopeHasOwner($query){
        return $query->whereNotNull('user_id');
    }

    /**
     * @param $query
     * @param array $userIds
     * @return mixed
     */
    public function scopeUserOneOf($query, $userIds = []){
        if($userIds){
            return $query->whereIn('user_id', $userIds);
        }
        return $query;
    }

    /**
     * @param $query
     * @param $productId
     * @return mixed
     */
    public function scopeProductId($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * @param $query
     * @param $userId
     * @return mixed
     */
    public function scopeUserId($query, $userId)
    {
       return $query->where('user_id', $userId);
    }

    /**
     * @param $query
     * @param $status
     * @return mixed
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * @param $query
     * @param $status
     * @return mixed
     */
    public function scopeNotStatus($query, $status){
        return $query->where('status', '!=', $status);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeWaitingDispatch($query)
    {
        return $query->status(IConversation::STATUS_WAITING_DISPATCH);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeDispatched($query)
    {
        return $query->status(IConversation::STATUS_DISPATCHED);
    }

    /**
     * @return HasMany
     */
    public function messages() : HasMany
    {
        return $this->hasMany('\App\Chat\Models\Message', 'conversation_id','id')->orderByDesc('timestamp');
    }

    /**
     * @return BelongsTo
     */
    public function product() : BelongsTo
    {
        return $this->belongsTo('\App\UserCapabilities\Models\PairProduct', 'product_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function user() : BelongsTo
    {
        return $this->belongsTo('\App\Http\Models\NusalUser', 'user_id', 'id');
    }

    public function alerts()
    {
        return $this->hasMany('App\Chat\Models\ConversationAlert', 'conversation_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function ticket() : BelongsTo
    {
        return $this->belongsTo('\App\ConnectWiseDataIntegration\Models\ConnectWiseTicket', 'ticket_id', 'id');
    }



    /**
     * @return mixed
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return mixed | Collection
     */
    public function getAlerts(){
        return $this->alerts;
    }

    /**
     * @param $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->product_id = $productId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * @return mixed | PairProduct
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @return mixed  | NusalUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return bool
     */
    public function hasUser(){
        return $this->getUser() !== null;
    }

    /**
     * @return mixed | Carbon
     */
    public function getStartDate()
    {
        return $this->created_at;
    }

    /**
     * @param $conversationIdentifier
     * @return $this
     */
    public function setConversationIdentifier($conversationIdentifier)
    {
        $this->conversation_identifier = $conversationIdentifier;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConversationIdentifier()
    {
        return $this->conversation_identifier;
    }

    /**
     * @param $identifier
     * @return $this
     */
    public function setContactIdentifier($identifier)
    {
        $this->contact_identifier = $identifier;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContactIdentifier()
    {
        return $this->contact_identifier;
    }

    /**
     * @param $channel
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    public function createTicket(){

        $ticketService = new TicketService();
        $product = $this->getProduct();
        $contactData = $this->getContactData();

        /**
         * @var $relatedBoard ProductTicketBoard
         */
        $relatedBoard = $product->boards()->defaultChatBoard()->first();

        $ticketArr = [
            'priority' => ['id' => PriorityService::MEDIUM_PRIORITY_ID],
            'source'   => ['id' => SourceService::CHAT_SOURCE_ID],
            'status'   => ['name' => TicketStatuses::NEW_TICKET],
            'owner'    => ['id' => $this->hasUser() ? $this->getUser()->getConnectWiseUserId() : null],
            'board'    => ['id' => $relatedBoard->getBoardId()],
            'company'  => ['id' => $contactData['company']['id']],
            'contact'  => ['id' => $contactData['id']],
            'summary'  => 'New incoming chat from '. $this->getContactIdentifier() .' at '. $this->getStartDate()->format('Y-m-d H:i \G\M\T')
        ];

        /**
         * @var $result ConnectWiseTicket
         */
        $createdTicket = $ticketService->saveFromArr($ticketArr);
        $this
            ->setRelationType(Conversation::AUTO_RELATION_TYPE)
            ->setTicketId($createdTicket->getId())
            ->save();

        if($this->hasUser()){
            event(new TicketCreatedOverlay($this->getUser(), $createdTicket));
        }

        return $createdTicket;
    }

    /**
     * @return mixed
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param $channelData
     * @return $this
     */
    public function setChannelData(array $channelData)
    {
        $this->channel_data = $channelData;
        return $this;
    }

    /**
     * @param array $channelData
     * @return $this
     */
    public function pushChannelData(array $channelData)
    {
        return $this->setChannelData(array_merge(
            $this->getChannelData() ?: [],
            $channelData
        ));
    }

    /**
     * @return mixed
     */
    public function getChannelData()
    {
        return $this->channel_data;
    }

    /**
     * @param array $additionalData
     * @return $this
     */
    public function setAdditionalData(array $additionalData)
    {
        $this->additional_data = $additionalData;
        return $this;
    }

    /**
     * @param array $additionalData
     * @return $this
     */
    public function pushAdditionalData(array $additionalData)
    {
        return $this->setAdditionalData(array_merge(
            $this->getAdditionalData() ?: [],
            $additionalData
        ));
    }

    /**
     * @return mixed
     */
    public function getAdditionalData()
    {
       return $this->additional_data;
    }

    /**
     * @param $contactData
     * @return $this
     */
    public function setContactData(array $contactData)
    {
        $this->contact_data = $contactData;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContactData()
    {
        return $this->contact_data;
    }


    /**
     * @return string
     */
    public function getContactName(){
        if($this->getContactIdentifier()){
            $firstName = isset($this->getContactData()['firstName']) ? $this->getContactData()['firstName'] : '';
            $lastName = isset($this->getContactData()['lastName']) ? $this->getContactData()['lastName'] : '';
            return $firstName . ' ' . $lastName;
        }
        return '';
    }

    /**
     * @return string
     */
    public function getContactCompanyName(){
        if($this->getContactIdentifier()){
            if(isset($this->getContactData()['company'])){
                return $this->getContactData()['company']['name'];
            }
        }
        return '';
    }

    /**
     * @param $ticketId
     * @return $this
     */
    public function setTicketId($ticketId)
    {
        $this->ticket_id = $ticketId;
        return $this;
    }


    /**
     * @return null|integer
     */
    public function getTicketId()
    {
        if($this->ticket_id){
            return $this->ticket_id;
        }

        return null;
    }

    /**
     * @param null $ticketId
     * @return bool
     */
    public function isRelatedTicket($ticketId = null)
    {
        if($ticketId){
            return $this->getTicketId() == $ticketId;
        }

        return !is_null($this->getTicketId());
    }

    /**
     * @return bool
     */
    public function haveRelatedTicket(){
        return $this->getTicketId() !== null;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return $this
     */
    public function setDispatched()
    {
        $this->status = static::STATUS_DISPATCHED;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDispatched() : bool
    {
        return $this->status === static::STATUS_DISPATCHED;
    }

    /**
     * Auto or manuel
     *
     * @param $relationType
     * @return $this
     */
    public function setRelationType($relationType)
    {
        if($relationType === self::AUTO_RELATION_TYPE || $relationType === self::MANUAL_RELATION_TYPE){
            $this->ticket_relation_type = $relationType;
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRelationType()
    {
        if($this->ticket_relation_type){
            return $this->ticket_relation_type;
        }

        return null;
    }

    /**
     * @return integer|null
     */
    public function getCompanyId()
    {
        if(isset($this->getContactData()['company'])){
            return $this->getContactData()['company']['id'];
        }
        return null;
    }

    /**
     * @return string|null
     */
    public function getCompanyName()
    {
        if(isset($this->getContactData()['company'])){
            return $this->getContactData()['company']['name'];
        }
        return null;
    }

    /**
     * @return mixed | Carbon
     */
    public function getAgentLastMessagedAt()
    {
        return $this->agent_last_messaged_at;
    }

    /**
     * @return mixed | Carbon
     */
    public function getCustomerLastMessagedAt()
    {
        return $this->customer_last_messaged_at;
    }

    /**
     * @param Carbon|null $timestamp
     * @return $this
     */
    public function setAgentLastMessagedAt($timestamp)
    {
        $this->agent_last_messaged_at = $timestamp;

        return $this;
    }

    /**
     * @return Conversation
     */
    public function touchAgentLastMessagedAt()
    {
        return $this->setAgentLastMessagedAt(now());
    }

    /**
     * @param null $timestamp
     * @return $this
     */
    public function setCustomerLastMessagedAt($timestamp)
    {
        $this->customer_last_messaged_at = $timestamp;
        return $this;
    }

    /**
     * @return Conversation
     */
    public function touchCustomerLastMessagedAt()
    {
        return $this->setCustomerLastMessagedAt(now());
    }

    /**
     * @param $ruleSeconds
     * @return bool
     */
    public function isCustomerLastMessageOvertimeRuleSeconds($ruleSeconds)
    {
        return now()->diffInSeconds($this->getCustomerLastMessagedAt()) >= $ruleSeconds;
    }

    /**
     * @param $ruleSeconds
     * @return bool
     */
    public function isAgentLastMessageOvertimeRuleSeconds($ruleSeconds)
    {
        return now()->diffInSeconds($this->getAgentLastMessagedAt()) >= $ruleSeconds;
    }

    /**
     * @param Builder $query
     * @param $ruleSeconds
     * @return Builder
     */
    public function scopeCustomerLastMessagedOlderThan(Builder $query, $ruleSeconds)
    {
        $lowerBound = now()->subSeconds($ruleSeconds);
        return $query->where('customer_last_messaged_at', '<=', $lowerBound);
    }
}
