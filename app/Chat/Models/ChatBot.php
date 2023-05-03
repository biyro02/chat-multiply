<?php

namespace App\Chat\Models;

use App\Chat\ChatBots\BaseBot;
use App\Chat\Contracts\IChannel;
use App\Chat\Contracts\IChatBot;
use App\Chat\Contracts\IConversation;
use App\UserCapabilities\Models\PairProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatBot extends Model
{
    use SoftDeletes;

    public $connection = 'mysql';
    public $table = 'chat_product_bots';
    public $timestamps = true;
    public $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo('\App\UserCapabilities\Models\PairProduct', 'product_id', 'id');
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
     * @param $className
     * @return $this
     */
    public function setClass($className)
    {
        $this->class = $className;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param IConversation $conversation
     * @param IChannel $channel
     * @return false|IChatBot
     */
    public function buildChatBot(IConversation $conversation, IChannel $channel)
    {
        $className = $this->getClass();

        if (class_exists($className)) {
            return new $className($channel, $conversation);
        }

        return false;
    }
}
