<?php

namespace App\Chat\Commands;

use App\CallManagement\Models\NuAnswerCompany;
use App\CallManagement\Services\RingCentral\SubscribeService;
use App\UserCapabilities\Models\PairProduct;
use Illuminate\Console\Command;

class SubscribeSmsWebHook extends Command
{
    /**
    * The name and signature of the console command.
    *
    * @var string
     */
    protected $signature = 'chat:subscribe:sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register SMS Channel Subscription';

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

            $this->line('Started At : ' . now('UTC')->format('Y-m-d H:i:s'));

            $products = PairProduct::with('nuAnswerCompanies')->get();

            /**
             * @var $product PairProduct
             */
            foreach ($products as $product) {

                if ($product->isTns()) {
                    continue;
                }

                $this->line($product->getName() . ' product checking');

                /**
                 * @var $nuAnswerCompany NuAnswerCompany
                 */
                foreach ($product->getNuAnswerCompanies() as $nuAnswerCompany) {

                    $this->line($nuAnswerCompany->getName() . ' is checking');
                    if ($nuAnswerCompany->getExtensionId()) {
                        $nuAnswerCompany->deleteExtensionSmsHook();
                        $this->line($product->getName() . ' old sms subscription deleted');
                        $nuAnswerCompany->subscribeExtensionSmsHook();
                        $this->line($product->getName() . ' sms subscription added');
                    }
                }
            }

            $this->line('Finished At : ' . now('UTC')->format('Y-m-d H:i:s'));

        } catch (\Exception $e) {
            $this->line($e->getMessage());
        }
    }
}
