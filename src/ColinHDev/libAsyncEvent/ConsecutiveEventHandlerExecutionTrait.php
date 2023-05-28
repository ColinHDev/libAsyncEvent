<?php

declare(strict_types=1);

namespace ColinHDev\libAsyncEvent;

use Generator;
use pocketmine\event\Event;
use pocketmine\event\HandlerListManager;
use pocketmine\event\RegisteredListener;
use RuntimeException;
use function assert;

trait ConsecutiveEventHandlerExecutionTrait {
    use CallbackHolderTrait;

    /** @var Generator<int, RegisteredListener, null, void> */
    private Generator $generator;
    private bool $blocked = false;

    public function call() : void {
        $this->generator = $this->getEventHandlers();
        $this->tryToResume();
    }

    public function block() : void {
        $this->blocked = true;
    }

    public function release() : void {
        $this->blocked = false;
        $this->tryToResume();
    }

    private function tryToResume() : void {
        $this->generator->next();
        if (!$this->generator->valid()) {
            $this->callCallback();
            return;
        }
        /** @var RegisteredListener $registration */
        $registration = $this->generator->current();
        assert($this instanceof Event);
        $registration->callEvent($this);
        if (!$this->blocked) {
            $this->tryToResume();
        }
    }

    /**
     * The following code used in this method was taken from
     * https://github.com/pmmp/PocketMine-MP/blob/stable/src/event/Event.php#L58-L68 and only slightly modified to a
     * generator function.
     * @return Generator<int, RegisteredListener, null, void>
     */
    private function getEventHandlers() : Generator {
        $handlerList = HandlerListManager::global()->getListFor(static::class);
        foreach ($handlerList->getListenerList() as $registration) {
            yield $registration;
        }
    }

    public function __destruct() {
        if ($this->generator->valid()) {
            throw new RuntimeException(
                static::class . " never finished handling all listeners. " .
                "Got stuck at a listener of plugin " . $this->generator->current()->getPlugin()->getName() . ". " .
                "(Probably a forgotten call of the event's release() method.) " .
                "Please contact the plugin author, if this is not your plugin."
            );
        }
    }
}