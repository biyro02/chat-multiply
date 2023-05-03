<?php

namespace App\Chat\Exceptions;

use Illuminate\Http\Response;

class InvalidPayloadException extends \Exception
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
                    'message' => 'Invalid payload.'
                ]
            ], Response::HTTP_BAD_REQUEST
        );
    }
}
