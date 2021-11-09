<?php

namespace TradeTree\Console\Traits;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Helper\ProgressBar as BaseBar;

trait ProgressBar
{
    protected $bar = false;

    protected function initBar($count)
    {
        BaseBar::setFormatDefinition('custom', ' %current%/%max% [%bar%] %percent:3s%% %message%');
        $this->bar = $this->output->createProgressBar($count);
        $this->bar->setFormat('custom');
        $this->bar->setMessage('');
    }

    /**
     * Increment progress bar
     */
    protected function advanceBar()
    {
        if ($this->bar) {
            $this->bar->advance();
        }
    }

    protected function informBar($message = '')
    {
        if ($this->bar) {
            $this->bar->setMessage($message);
        }
    }

    /**
     * Increment progress bar
     */
    protected function subBar()
    {
        if ($this->bar) {
            $this->bar->advance(-1);
        }
    }

    /**
     * Finish progress bar
     */
    protected function finishBar()
    {
        if ($this->bar) {
            $this->bar->finish();
            $this->info('');
        }
    }

    /**
     * Handles command execution log
     *
     * @param $string
     * @param string $action
     */
    protected function log($string, $action = 'info')
    {
        Log::$action(print_r($string, true));
        $this->debug($string);
    }

    /**
     * Logs command execution into console
     *
     * @param $string
     */
    protected function debug($string, $action = 'info')
    {
        if ($this->debug) {
            $this->{$action}(' ' . print_r($string, true));
        }
    }
}