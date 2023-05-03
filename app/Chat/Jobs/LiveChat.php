<?php

namespace App\Chat\Jobs;

use App\Chat\Channels\BaseChannel;
use App\Http\Repositories\PairProduct\IPairProductRepository;
use App\UserCapabilities\Models\PairProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LiveChat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    protected $channel;

    /**
     * @var string
     */
    protected $product;

    /**
     * @var array
     */
    protected $payload;

    /**
     * LiveChat constructor.
     * @param $channel
     * @param $product
     * @param $payload
     */
    public function __construct($product, $channel, $payload)
    {
        $this->queue   = 'livechat';
        $this->channel = $channel;
        $this->product = $product;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @param IPairProductRepository $repository
     * @return void
     */
    public function handle(IPairProductRepository $repository)
    {
        try {

            $product = $repository->firstOrFailWithSlug($this->product);
            /**
             * @var $channel BaseChannel
             */
            $channel = BaseChannel::make($this->channel, $product);

            $channel->handleIncomingMessage($this->payload);

        } catch (\Throwable $t) {
            echo $t->getMessage();
            echo $t->getTraceAsString();
        }

    }
}
