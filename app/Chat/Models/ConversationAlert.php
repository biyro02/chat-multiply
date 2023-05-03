<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/13/21
 * Time: 3:05 PM
 */

namespace App\Chat\Models;


use App\Chat\Alert\AlertType\Conversation\AlertType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConversationAlert extends Model
{

    use SoftDeletes;

    protected $table = 'chat_conversation_alerts';
    protected $connection = 'mysql';
    protected $guarded = [];

    protected $dates = [
        'alert_timestamp'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('conversation', function($query){
            $query->has('conversation');
        });
    }

    public function toArray()
    {
        $agent = $this->getConversation()->getUser();
        return [
            'agent_name' => $agent ? $agent->getFullName() : 'N/A',
            'team_lead' => '',
            'alert_duration' => $this->getAlertTimeStamp()->format('Y-m-d H:i:s'),
            'product' => $this->getConversation()->getProduct()->getName(),
            'company_name' => $this->getConversation()->getContactCompanyName(),
            'contact_name' => $this->getConversation()->getContactName(),
            'alert_message' => $this->getAlertMessage()
        ];
    }

    public function getAlertMessage()
    {
        $alertTypeClass = $this->getAlertType()->getClass();
        /**
         * @var $ourAlertTypeObject AlertType
         */
        $ourAlertTypeObject = new $alertTypeClass();
        $ourAlertTypeObject->setModelData($this->getAlertType());

        return $ourAlertTypeObject->description($this);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function conversation(){
        return $this->belongsTo('\App\Chat\Models\Conversation', 'conversation_id', 'id');
    }

    public function alertType(){
        return $this->belongsTo('\App\Chat\Models\ConversationAlertType', 'alert_type_id', 'id');
    }

    /**
     * @param $query
     * @param $alertTypeId
     * @return mixed
     */
    public function scopeAlertTypeId($query, $alertTypeId){
        return $query->where('alert_type_id', '=', $alertTypeId);
    }

    /**
     * @param $query
     * @param $conversationId
     * @return mixed
     */
    public function scopeConversationId($query, $conversationId){
        return $query->where('conversation_id', '=', $conversationId);
    }

    /**
     * @param $query
     * @param array $conversationIds
     * @return mixed
     */
    public function scopeConversationOneOf($query, $conversationIds = []){
        if($conversationIds){
            return $query->whereIn('conversation_id', $conversationIds);
        }
        return $query;
    }
    /**
     * @return mixed | Conversation
     */
    public function getConversation(){
        return $this->conversation;
    }

    /**
     * @return mixed | ConversationAlertType
     */
    public function getAlertType(){
        return $this->alertType;
    }

    /**
     * @param Carbon $alertTimestamp
     * @return $this
     */
    public function setAlertTimeStamp(Carbon $alertTimestamp){
        $this->alert_timestamp = $alertTimestamp;
        return $this;
    }

    /**
     * @return mixed | Carbon
     */
    public function getAlertTimeStamp(){
        return  $this->alert_timestamp;
    }

    /**
     * @param $alertTypeId
     * @return $this
     */
    public function setAlertTypeId($alertTypeId){
        $this->alert_type_id = $alertTypeId;
        return $this;
    }

    /**
     * @param $conversationId
     * @return $this
     */
    public function setConversationId($conversationId){
        $this->conversation_id = $conversationId;
        return $this;
    }
}
