<?php

declare(strict_types=1);

namespace ColinHDev\libAsyncEvent;

use Generator;
use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\event\HandlerListManager;
use pocketmine\event\RegisteredListener;
use RuntimeException;
use function array_map;
use function assert;
use function count;
use function implode;

trait PrioritizedEventHandlerExecutionTrait {

    use CallbackHolderTrait;

    /** @var Generator<int, array<int, RegisteredListener>, null, void> */
    private Generator $generator;
    private int $blocks = 0;

    public function call() : void {
        $this->generator = $this->getEventHandlers();
        $this->tryToResume();
    }

    public function block() : void {
        $this->blocks++;
    }

    public function release() : void {
        $this->blocks--;
        if ($this->blocks === 0) {
            $this->generator->next();
            $this->tryToResume();
        }
    }

    private function tryToResume() : void {
        $this->generator->next();
        if (!$this->generator->valid()) {
            $this->callCallback();
            return;
        }
        /** @var array<int, RegisteredListener> $registrations */
        $registrations = $this->generator->current();
        foreach ($registrations as $registration) {
            assert($this instanceof Event);
            $registration->callEvent($this);
        }
        if ($this->blocks === 0) {
            $this->generator->next();
            $this->tryToResume();
        }
    }

    /**
     * The following code used in this method was taken from
     * https://github.com/pmmp/PocketMine-MP/blob/stable/src/event/Event.php#L58-L68 and only slightly modified to a
     * generator function.
     * @return Generator<int, array<int, RegisteredListener>, null, void>
     */
    private function getEventHandlers() : Generator {
        $handlerList = HandlerListManager::global()->getListFor(static::class);
        foreach(EventPriority::ALL as $priority) {
            yield $handlerList->getListenersByPriority($priority);
        }
    }

    public function __destruct() {
        if ($this->generator->valid()) {
            $plugins = array_map(
                static function(RegisteredListener $registration) : string {
                    return $registration->getPlugin()->getName();
                },
                $this->generator->current()
            );
            throw new RuntimeException(
                static::class . " never finished handling all listeners. " .
                "Got stuck at a listener of one of the following plugins: " . implode(", ", $plugins) . ". " .
                "(Probably a forgotten call of the event's release() method.) " .
                "Please contact the author of the faulty plugin, if this is not your plugin."
            );
        }
    }
}