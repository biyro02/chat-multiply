<?php

namespace App\Chat\ChatBots;

use App\Chat\Contracts\IChannel;
use App\Chat\Contracts\IChatBot;
use App\Chat\Contracts\IChatBotStep;
use App\Chat\Contracts\IConversation;
use App\Chat\Contracts\IMessage;
use App\Chat\Models\Conversation;
use App\Chat\Models\Message;
use App\UserCapabilities\Models\PairProduct;

abstract class BaseChatBot implements IChatBot
{
    const BOT_NAME = 'BOT';

    /**
     * @var IChannel
     */
    protected $channel;

    /**
     * @var Conversation
     */
    protected $conversation;

    /**
     * BaseBot constructor.
     * @param IChannel $channel
     * @param IConversation $conversation
     */
    public function __construct(IChannel $channel, IConversation $conversation)
    {
        $this->channel = $channel;
        $this->conversation = $conversation;
    }

    /**
     * @return IChannel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return IConversation
     */
    public function getConversation()
    {
        return $this->conversation;
    }

    /**
     * @return PairProduct
     */
    public function getProduct()
    {
        return $this->getConversation()->getProduct();
    }

    /**
     * @return IChatBotStep
     */
    protected abstract function getEntryStepClassName();

    /**
     * @return mixed|null
     */
    protected function getCurrentStepClassName()
    {
        $additionalData = $this->getConversation()->getAdditionalData();

        return isset($additionalData['bot_step']) ? $additionalData['bot_step'] : null;
    }

    /**
     * @param IChatBotStep $step
     * @return bool
     */
    protected function shouldRunCallback(IChatBotStep $step)
    {
        $stepClass = $this->getCurrentStepClassName();

        return get_class($step) === $stepClass;
    }

    /**
     * @return IChatBotStep
     */
    protected function getCurrentStepClass() : IChatBotStep
    {
        $stepClass = $this->getCurrentStepClassName();

        if (!$stepClass) {
            $stepClass = $this->getEntryStepClassName();
        }

        return $this->buildStepClass($stepClass);
    }

    /**
     * @param $className
     * @return mixed
     */
    protected function buildStepClass($className)
    {
        if (class_exists($className)) {
            $stepClass = new $className($this);

            if ($stepClass instanceof BaseChatBotStep) {
                return $stepClass;
            }
        }

        return false;
    }

    /**
     * @param IChatBotStep $step
     */
    protected function setCurrentStep(IChatBotStep $step)
    {
        $this->getConversation()->pushAdditionalData([
            'bot_step' => get_class($step)
        ])->save();
    }

    /**
     * @param IMessage $message
     * @return mixed
     */
    public function reply(IMessage $message)
    {
        $message->setSender(static::BOT_NAME);
        return $this->getChannel()->sendMessage($this->getConversation(), $message);
    }

    /**
     * @param $text
     * @return mixed
     */
    public function replyText($text)
    {
        $message = new Message();
        $message->setMessage($text);
        return $this->reply($message);
    }

    /**
     * @return void
     */
    public function dispatchConversation()
    {
        $this->getConversation()->setStatus(IConversation::STATUS_WAITING_DISPATCH)->save();
    }

    /**
     * @param IMessage $message
     * @return void
     */
    public function handle(IMessage $message)
    {
        if ($this->getConversation()->getStatus() !== IConversation::STATUS_BOT) {
            $this->getConversation()->setStatus(IConversation::STATUS_BOT)->save();
        }

        $step = $this->getCurrentStepClass();

        $nextStep = $step;
        if ($this->shouldRunCallback($step)) {
            $nextStep = null;
            if ($nextStepClass = $step->callback($message)) {
                $nextStep = $this->buildStepClass($nextStepClass);
            }
        }

        if ($nextStep) {
            $nextStep->handle($message);
            $this->setCurrentStep($nextStep);
        } else {
            // If the step class invalid
            $this->replyText('An unknown error has occurred! Please try again.');
            $this->getChannel()->endConversation($this->getConversation(), IConversation::END_CAUSE_INTERNAL_ERROR);
        }
    }

}
