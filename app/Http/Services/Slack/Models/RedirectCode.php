<?php
/**
 * Created by PhpStorm.
 * User: bayramu
 * Date: 3/24/21
 * Time: 3:59 PM
 */

namespace App\Http\Services\Slack\Models;

/**
 * Class User
 * @package App\Http\Services\Slack\Models
 * @method string getCode()
 * @method string getState()
 */
class RedirectCode extends BaseModel
{
    /**
     * @var array
     */
    public $fields = [
        "code" => "",
        "state" => null,
    ];
}