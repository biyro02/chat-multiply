<?php
/**
 * Created by PhpStorm.
 * User: bayramu
 * Date: 4/13/21
 * Time: 3:52 PM
 */

namespace App\Chat\Commands;

use App\Chat\Channels\BaseChannel;
use App\Chat\ChatBots\BaseChatBot;
use App\Chat\Contracts\IConversation;
use App\Chat\Models\Conversation;
use App\Chat\Models\Message;
use App\Http\Models\SystemConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CheckConversationsExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'conversation:checkExpiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check conversation expiration depends on customer response';


    private $messageTemplateText = 'Your chat will be expired in {minute_text} if you don\'t reply anymore';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * If customer overtime by rule seconds end conversation
     */
    public function handle()
    {

        try {

            $limitMinutes = SystemConfig::getLiveChatOvertimeLimit();
            /**
             * @var $activeConversations Collection
             */
            $activeConversations = Conversation::dispatched()->get();

            $this->line($activeConversations->count() . ' conversation found');

            $activeConversations->each(function (Conversation $conversation) use ($limitMinutes)
            {
                $lastCustomerResponded = $conversation->getCustomerLastMessagedAt();
                $this->line('Conversation Customer last responded at : ' . $lastCustomerResponded);
                /**
                 * Now diff last responded gives us how many minutes passed after last response
                 */
                if(now() >= $lastCustomerResponded){

                    $lastResponseAgeAsMinutes = now()->diffInMinutes($lastCustomerResponded);
                    $this->line($lastResponseAgeAsMinutes . ' minute passed after customer last response');

                    /**
                     * Limit Exceeded, End Conversation
                     */
                    if($lastResponseAgeAsMinutes >= $limitMinutes){

                        $this->line('Conversation with id : ' . $conversation->getKey() . ' expired');
                        $channel = BaseChannel::make($conversation->getChannel(), $conversation->getProduct());
                        $channel->endConversation($conversation, IConversation::END_CAUSE_CUSTOMER_LIMIT_EXCEEDED);

                    }elseif($lastResponseAgeAsMinutes >= 1){
                        /**
                         * Limit Not Exceeded, Reminder Messages To Customer
                         */
                        $remainingMinutesToExpire = $limitMinutes - $lastResponseAgeAsMinutes;
                        $this->line($remainingMinutesToExpire . ' minutes left to expire conversation');
                        $minuteText = $remainingMinutesToExpire . ' ' . ($remainingMinutesToExpire === 1 ? 'minute' : 'minutes');
                        $text = str_replace('{minute_text}', $minuteText, $this->messageTemplateText);
                        /**
                         * Create Message
                         */
                        $message = new Message();
                        $message->setSender(BaseChatBot::BOT_NAME);
                        $message->setMessage($text);
                        /**
                         * Create Channel And Send Message
                         */
                        $channel = BaseChannel::make($conversation->getChannel(), $conversation->getProduct());
                        $channel->sendMessage($conversation, $message);
                    }else{
                        $this->line('No need for any reminder message');
                    }
                }else{
                    $this->line('Corrupted last responded date, customer last responded must not bigger than now()');
                }
            });
        }catch (\Throwable $t) {

        }
    }
}
