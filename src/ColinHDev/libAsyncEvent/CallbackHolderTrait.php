<?php

declare(strict_types=1);

namespace ColinHDev\libAsyncEvent;

use Closure;

trait CallbackHolderTrait {
    
    /** @var null|Closure($this) : void */
    private ?Closure $callback = null;

    /**
     * @param Closure($this) : void $callback
     */
    public function setCallback(Closure $callback) : void {
        $this->callback = $callback;
    }

    public function callCallback() : void {
        if ($this->callback !== null) {
            ($this->callback)($this);
            $this->callback = null;
        }
    }
}