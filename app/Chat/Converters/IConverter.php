<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/13/21
 * Time: 9:27 AM
 */

namespace App\Chat\Converters;


use App\Chat\Contracts\IConversation;

interface IConverter
{

    const INLINE_IMAGE_PATH = 'https://na.myconnectwise.net/v4_6_release/api/inlineimages/Numsp/';

    /**
     * @param IConversation $conversation
     * @return mixed
     */
    public function convert(IConversation $conversation);
}
