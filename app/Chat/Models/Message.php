<?php

namespace App\Chat\Models;

use App\Chat\Contracts\IConversation;
use App\Chat\Contracts\IMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

/**
 * Class Message
 * @package App\Chat\Models
 * @method Message|Builder incoming()
 * @method Message|Builder outgoing()
 * @method Message|Builder direction()
 */
class Message extends Model implements IMessage
{
    use SoftDeletes;

    const DIRECTION_INCOMING = 'incoming';
    const DIRECTION_OUTGOING = 'outgoing';

    public $primaryKey = '_id';
    public $connection = "mongodb";
    public $table = "chat_messages";
    public $collection = "chat_messages";
    public $timestamps = true;
    public $guarded = [];

    public $with = [
        'attachments'
    ];

    public $casts = [
        'timestamp' => 'datetime'
    ];

    public $hidden = [
        'raw'
    ];

    public $appends = [
        'date'
    ];

    public static function boot()
    {
        parent::boot();
        static::saved(function($model){
            /**
             * @var $model static
             */
            $model->getAttachments()->each(function($attachment) use($model){
                $model->attachments()->save($attachment);
            });
        });
    }
    /**
     * @return BelongsTo|Builder|Conversation
     */
    public function conversation()
    {
        return $this->belongsTo('\App\Chat\Models\Conversation', 'conversation_id', 'id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\Jenssegers\Mongodb\Relations\HasMany
     */
    public function attachments()
    {
        return $this->hasMany('\App\Chat\Models\Attachment', 'message_id', '_id');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeIncoming($query)
    {
        return $this->scopeDirection($query, static::DIRECTION_INCOMING);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeOutgoing($query)
    {
        return $this->scopeDirection($query, static::DIRECTION_OUTGOING);
    }

    /**
     * @return mixed
     */
    public function getDateAttribute()
    {
        return $this->getTimestamp()->format('H:i F d');
    }

    /**
     * @param $query
     * @param $direction
     * @return mixed
     */
    public function scopeDirection($query, $direction)
    {
        return $query->where('direction', $direction);
    }

    /**
     * @return mixed | IConversation
     */
    public function getConversation(): Conversation
    {
        return $this->conversation;
    }

    /**
     * @param array $attachments
     * @return $this
     */
    public function setAttachments(array $attachments)
    {
        $this->setRelation('attachments', collect($attachments));
        return $this;
    }

    /**
     * @return mixed | Collection
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param $recipient
     * @return $this
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param $timestamp
     * @return $this
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return $this
     */
    public function setIncoming()
    {
        $this->direction = static::DIRECTION_INCOMING;
        return $this;
    }

    /**
     * @return $this
     */
    public function setOutgoing()
    {
        $this->direction = static::DIRECTION_OUTGOING;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIncoming() : bool
    {
        return $this->getDirection() === static::DIRECTION_INCOMING;
    }

    /**
     * @return bool
     */
    public function isOutgoing() : bool
    {
        return $this->getDirection() === static::DIRECTION_OUTGOING;
    }

    /**
     * @return mixed
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param $sender
     * @return $this
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setRaw($data)
    {
        $this->raw = $data;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRaw()
    {
        return $this->raw;
    }
}
