<?php

namespace App\Chat\Exceptions;

use Illuminate\Http\Response;

class InvalidTicketRelationType extends \Exception
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function render(){
        return response()->json(
            [
                'status' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'data' => [
                    'message' => 'This ticket related with auto relation type. You can not update it!',
                ]
            ], Response::HTTP_NOT_FOUND
        );
    }
}
