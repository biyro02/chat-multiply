<?php

namespace App\Chat\Controllers;

use App\Chat\Channels\MicrosoftTeams;
use App\Chat\Channels\Slack;
use App\Chat\Contracts\IConversation;
use App\Chat\Exceptions\InvalidTicketRelationRequest;
use App\Chat\Exceptions\InvalidTicketRelationType;
use App\Chat\Jobs\LiveChat;
use App\Chat\Models\AppCredentials;
use App\Chat\Requests\ChatMessageRequest;
use App\Http\Constants\MessageTypes;
use App\Http\Models\NusalUser;
use App\Http\Repositories\PairProduct\IPairProductRepository;
use App\Http\Services\Slack\AuthorizationService;
use App\Onboarding\Exceptions\TicketNotFound;
use App\TicketManagement\Factories\TicketProviderFactory;
use App\UserCapabilities\Models\PairProduct;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Chat\Channels\BaseChannel;
use App\Chat\Models\Conversation;
use App\Chat\Models\Message;
use Illuminate\Http\Response;

class ChatController extends Controller
{

    /**
     * @var IPairProductRepository|null
     */
    protected $pairProductRepository = null;

    /**
     * ChatController constructor.
     * @param IPairProductRepository $pairProductRepository
     */
    public function __construct(IPairProductRepository $pairProductRepository)
    {
        $this->pairProductRepository = $pairProductRepository;
    }

    /**
     * @param string $channelName
     * @param PairProduct $product
     * @return MicrosoftTeams|Slack
     * @throws \App\Chat\Exceptions\InvalidChannelException
     */
    private function getChannel(string $channelName, PairProduct $product)
    {
        return BaseChannel::make($channelName, $product);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function webhook(Request $request)
    {
        if ($request->get('channel') === BaseChannel::SLUG_SLACK && $request->get('challenge')) {
            return response()->json([
                'challenge' => $request->get('challenge')
            ]);
        } else if ($request->get('channel') === BaseChannel::SLUG_SMS && $request->header('Validation-Token')) {
            return response()->json([
               'success' => true
            ])->header(
                'Validation-Token',
                $request->header('Validation-Token')
            );
        }

        $payload = $request->json()->all();
        syslog(LOG_INFO, json_encode($payload));

        LiveChat::dispatch($request->get('product'), $request->get('channel'), $payload);

        return response()->json([
            'success' => true,
            'data' => $request->all()
        ]);
    }

    /**
     * @param Request $request
     */
    public function appCreateWebhook(Request $request)
    {
        try {
            /**
             * @var PairProduct $product
             */
            $product = $this->pairProductRepository->firstOrFailWithSlug($request->product);
            $channel = $this->getChannel($request->channel, $product);
            $authorization = $channel->authorize($request->code);

            if($authorization->isOk())
            {
                $app = AppCredentials::channel($request->channel)
                    ->teamId($authorization->getTeamId())
                    ->appId($authorization->getAppId())
                    ->first();
                /**
                 * Create new appCredentials by the last requested appCredentials
                 */
                if(!$app){
                    $app = AppCredentials::createCredentialsByAccessResponse($authorization, $request->channel, $channel->getAppCredentials());
                }

                $app->setAccessToken($authorization->getAccessToken())->save();

                // TODO: tam bu araya bir "başarılı" mesajı gösteren sayfa eklenmeli
                AuthorizationService::returnToApp($authorization->getAppId());
            }
        } catch (\Throwable $throwable) {
            AuthorizationService::returnToSlack($throwable->getMessage());
        }
    }

    /**
     * @param Conversation $conversation
     * @param ChatMessageRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Chat\Exceptions\InvalidChannelException
     */
    public function sendConversationMessage(Conversation $conversation, ChatMessageRequest $request)
    {
        $channel = $this->getChannel($conversation->getChannel(), $conversation->getProduct());

        /**
         * @var $agent NusalUser
         */
        $agent = $request->user();

        $message = new Message();
        $message->setSender($agent->getFullName());
        $message->setMessage($request->input('message'));

        if ($request->hasFile('files')) {
            $files = $request->file('files');
            $files = is_array($files) ? $files : [$files];
            $channel->transformOutgoingAttachments($message, $files);
        }

        $channel->sendMessage($conversation, $message);

        $response = $message->toArray();
        $response['transactionId'] = $request->get('transactionId');

        return response()->json([
            'status' => Response::HTTP_OK,
            'success' => true,
            'type' => MessageTypes::LIVE_CHAT_SEND_MESSAGE,
            'data' => $response
        ], Response::HTTP_OK);
    }

    /**
     * @param Conversation $conversation
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConversation(Conversation $conversation)
    {
        $conversation->load('messages');

        return response()->json([
            'status' => Response::HTTP_OK,
            'success' => true,
            'type' => MessageTypes::LIVE_CHAT_GET_CONVERSATION,
            'data' => $conversation
        ], Response::HTTP_OK);
    }

    /**
     * @param Conversation $conversation
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConversationMessages(Conversation $conversation)
    {
        $conversation->load('messages');

        return response()->json([
            'status' => Response::HTTP_OK,
            'success' => true,
            'type' => MessageTypes::LIVE_CHAT_GET_CONVERSATION,
            'data' => $conversation->messages
        ], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAgentConversations(Request $request)
    {
        $conversations = Conversation::userId($request->user()->getKey())->get();

        return response()->json([
            'status' => Response::HTTP_OK,
            'success' => true,
            'type' => MessageTypes::LIVE_CHAT_AGENT_CONVERSATIONS,
            'data' => $conversations
        ], Response::HTTP_OK);
    }

    /**
     * @param Conversation $conversation
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function endConversation(Conversation $conversation)
    {
        $channel = $this->getChannel($conversation->getChannel(), $conversation->getProduct());

        $channel->endConversation($conversation, IConversation::END_CAUSE_AGENT_MANUAL_END);

        return response()->json([
            'status' => Response::HTTP_OK,
            'success' => true,
            'message' => 'Conversation finished successfully.',
            'data' => [
                'conversation' => $conversation
            ]
        ], Response::HTTP_OK);
    }

    /**
     * @param Conversation $conversation
     * @param $ticketId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws InvalidTicketRelationType
     * @throws TicketNotFound
     */
    public function relateTicket(Conversation $conversation, $ticketId, Request $request)
    {
        $ticket = TicketProviderFactory::forNuRD()->ticketDetail($ticketId);
        if(!$ticket){
            throw new TicketNotFound($ticketId);
        }

        /**
         * If conversation is set any ticket and relation type is auto, this process must be end
         */
        if($conversation->getRelationType() === Conversation::AUTO_RELATION_TYPE && $conversation->getTicketId()){
            throw new InvalidTicketRelationType();
        }

        $conversation->setTicketId($ticketId)->setRelationType($request->get('ticket_relation_type', Conversation::AUTO_RELATION_TYPE))->save();

        return response()->json([
            'status' => 200,
            'message' => 'Conversation related with ticket successfully.',
        ], Response::HTTP_OK);
    }

    /**
     * @param Conversation $conversation
     * @param $ticketId
     * @return \Illuminate\Http\JsonResponse
     * @throws InvalidTicketRelationRequest
     * @throws InvalidTicketRelationType
     * @throws TicketNotFound
     */
    public function deleteTicketRelation(Conversation $conversation, $ticketId)
    {
        if(!$conversation->getTicketId() || !$conversation->getRelationType()){
            throw new InvalidTicketRelationRequest($conversation->getKey(), $ticketId);
        }

        $ticket = TicketProviderFactory::forNuRD()->ticketDetail($ticketId);
        if(!$ticket){
            throw new TicketNotFound($ticketId);
        }

        /**
         * If conversation is set any ticket and relation type is auto, this process must be end
         */
        if($conversation->getRelationType() === Conversation::AUTO_RELATION_TYPE && $conversation->getTicketId()){
            throw new InvalidTicketRelationType();
        }

        $conversation->setTicketId(null)->setRelationType(null)->save();

        return response()->json([
            'status' => 200,
            'message' => 'Conversation ticket relation deleted successfully.',
        ], Response::HTTP_OK);
    }

}
