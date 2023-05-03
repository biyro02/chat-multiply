<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/11/20
 * Time: 7:37 PM
 */

namespace App\Http\Services\Outlook;
use App\Http\Models\Outlook\OutlookUserServiceModel;
use Microsoft\Graph\Graph;

/* bu arkadaş daha userı bizim tarafımızda tanımlayamadan direk tokenla çalışmaktadır */
class OutlookServiceForToken
{

    /**
     * @var null
     */
    private $token = null;

    /**
     * OutlookServiceForToken constructor.
     * @param $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function me(){

        $graph = new Graph();
        $graph->setAccessToken($this->token);

        try{

            $user = $graph->createRequest('GET', '/me')
                ->setReturnType(OutlookUserServiceModel::class)
                ->execute();

        }catch(\Throwable $t){
            echo $t->getMessage();
        }

        return $user;

    }
}
