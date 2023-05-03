<?php

namespace App\Chat\Exceptions;

use Illuminate\Http\Response;

class InvalidChannelException extends \Exception
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function render(){
        return response()->json(
            [
                'status' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'data' => [
                    'message' => 'Invalid channel.'
                ]
            ], Response::HTTP_BAD_REQUEST
        );
    }
}
