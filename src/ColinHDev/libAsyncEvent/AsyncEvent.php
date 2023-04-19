<?php

declare(strict_types=1);

namespace ColinHDev\libAsyncEvent;

use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\event\HandlerListManager;
use pocketmine\event\RegisteredListener;

abstract class AsyncEvent extends Event {

    /** @phpstan-var \Generator<int, RegisteredListener, null, void> */
    private \Generator $generator;
    private ?\Closure $callback = null;

    /**
     * @phpstan-param \Closure(static): void|null $callback
     */
    public function setCallback(?\Closure $callback) : void {
        $this->callback = $callback;
    }

    public function call() : void {
        $this->generator = $this->getEventHandlers();
        $this->tryToResume();
    }

    public function continue() : void {
        $this->generator->next();
        $this->tryToResume();
    }

    public function __destruct() {
        if ($this->generator->valid()) {
            throw new \RuntimeException(
                $this->getEventName() . " never finished handling all listeners. Got stuck at a listener of plugin " . $this->generator->current()->getPlugin()->getName() . ". (Possibly a forgotten call of the event's continue() method.) Please contact the plugin author, if this is not your plugin."
            );
        }
    }

    private function tryToResume() : void {
        if (!$this->generator->valid()) {
            if ($this->callback !== null) {
                ($this->callback)($this);
                $this->callback = null;
            }
            return;
        }
        /** @phpstan-var RegisteredListener $registration */
        $registration = $this->generator->current();
        $registration->callEvent($this);
    }

    /**
     * The following code used in this method was taken from
     * https://github.com/pmmp/PocketMine-MP/blob/stable/src/event/Event.php#L58-L67 and only slightly modified to a
     * generator function.
     * @phpstan-return \Generator<int, RegisteredListener, null, void>
     */
    private function getEventHandlers() : \Generator {
        $handlerList = HandlerListManager::global()->getListFor(get_class($this));
        foreach($handlerList->getListenerList() as $registration){
            yield $registration;
        }
    }
}