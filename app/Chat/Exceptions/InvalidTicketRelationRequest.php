<?php

namespace App\Chat\Exceptions;

use Illuminate\Http\Response;

class InvalidTicketRelationRequest extends \Exception
{
    protected $ticketId = null;
    protected $conversationId = null;

    public function __construct($conversationId, $ticketId)
    {
        parent::__construct();
        $this->ticketId = $ticketId;
        $this->conversationId = $conversationId;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function render(){
        return response()->json(
            [
                'status' => Response::HTTP_FORBIDDEN,
                'success' => false,
                'data' => [
                    'message' => 'There is no relation between conversationId: ' . $this->conversationId . ' and ticketId: ' . $this->ticketId . '.',
                ]
            ], Response::HTTP_FORBIDDEN
        );
    }
}
