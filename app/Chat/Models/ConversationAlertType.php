<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/13/21
 * Time: 3:03 PM
 */

namespace App\Chat\Models;


use App\Http\Models\ActiveAble;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConversationAlertType extends Model
{

    use SoftDeletes, ActiveAble;

    protected $table = 'chat_conversation_alert_types';
    protected $connection = 'mysql';
    protected $guarded = [];


    protected $casts = [
       'attributes' => 'json',
       'is_active' => 'boolean'
    ];
    /**
     * @return mixed
     */
    public function getClass(){
        return $this->class;
    }

    /**
     * @return mixed
     */
    public function getAlertViewTemplateText(){
        return $this->alert_view_template_text;
    }
}
