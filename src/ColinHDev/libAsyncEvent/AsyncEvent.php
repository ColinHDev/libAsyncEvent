<?php

declare(strict_types=1);

namespace ColinHDev\libAsyncEvent;

use Closure;

interface AsyncEvent {

    /**
     * 
     * @param Closure($this) : void $callback
     */
    public function setCallback(Closure $callback) : void;
    
    public function block() : void;
    
    public function release() : void;
}