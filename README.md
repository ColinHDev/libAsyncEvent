# libAsyncEvent

libAsyncEvent provides you with multiple implementations for creating asynchronous event execution for PocketMine-MP 
plugins.

## Why should I use this library?
I came to the idea for this library while implementing [libasynql](https://github.com/poggit/libasynql) and 
[await-generator](https://github.com/SOF3/await-generator) to my plugin [CPlot](https://github.com/ColinHDev/CPlot). I 
then realised how annoying it is, to deal with [PocketMine-MP's](https://github.com/pmmp/PocketMine-MP) events in this 
case, since it is not possible e.g. to check if a player is allowed to build in a certain area with an asynchronous-run 
query.

So when deciding to implement custom events into the plugin, I wanted to make it as developer-friendly as possible. So 
when someone decides to work with the events, they are not forced to directly decide, how to react (e.g. cancelling the 
event) and be allowed e.g. to run asynchronous queries to validate their decision.

## How to use this library in my plugin?
### How should my event class look like?
Normally your event class looks like this: It extends PocketMine-MP's `Event` class or one of its subclasses and maybe 
also implements an interface like the `Cancellable` one.
```php
use pocketmine\event\Event;
use pocketmine\event\Cancellable;

class MyEvent extends Event implements Cancellable {}
```
First, implement the `AsyncEvent` interface, which this library provides, to your event class.
And second, use one of the `EventHandlerExecutionTrait`s in your event class:
```php
use ColinHDev\libAsyncEvent\AsyncEvent;
use ColinHDev\libAsyncEvent\SomeEventHandlerExecutionTrait;
use pocketmine\event\Event;
use pocketmine\event\Cancellable;

class MyEvent extends Event implements AsyncEvent, Cancellable {
    use SomeEventHandlerExecutionTrait;
}
```
There are multiple `EventHandlerExecutionTrait`s, which you can use and each of them has a different behaviour:
- `ConsecutiveEventHandlerExecutionTrait`: This trait will execute all event listeners one after another. If one 
`block()`s the execution, the next listener will only be executed after the current one calls `release()`.
- `PriorityEventHandlerExecutionTrait`: This trait will execute all event listeners of the same priority at the same 
time. If one or more listeners `block()` the execution, the listeners of the higher priority will only be executed after
all of the `block()`ing ones called `release()`.

### How can I call my event?
You can simply call your event by creating a new event instance and using its `call()` method.
```php
$event = new MyEvent();
$event->call();
```
But to get the result of the event, you need to provide a callback function which will be run when all listeners are finished.
```php
$event = new MyEvent();
$event->setCallback(
    function (MyEvent $event) : void {
        if ($event->isCancelled()) {
            // do something
        } else {
            // do something else
        }
    }
);
$event->call();
```

### How to improve my event class?
Unless both you and the person trying to register a listener for your async event use composer, they will not be able to
correctly see libAsyncEvent's declared methods like `block()` or `release()` in their IDE.

Although ideally composer should be used to develop plugins and to declare their dependencies, we can not force anyone 
to do so. So to make it easier for them, you can add following PHPDoc comments to your event class:
```php
/**
 * @link https://github.com/ColinHDev/libAsyncEvent/
 * @method void block()
 * @method void release()
 */
class MyEvent extends Event implements AsyncEvent, Cancellable {}
```
This way, the IDE will know that these methods exist and will not show any errors.

## How to handle an event made with this library?
To register an event listener for an async event, you can do it the same way, you would normally do it for every other event listener:
```php
Server::getInstance()->getPluginManager()->registerEvents(new MyListener(), $this);
```
In this class, you as well create a method which accepts the event, you want to listen to, as a parameter:
```php
    public function onMyEvent(MyEvent $event) : void {
        // do something
    }
```
So basically there is no difference between a normal event listener and our async event listener.
But if you want to keep the event instance from continuing with its execution, you need to call the `block()` method.
This way the event won't finish its execution until you call the `release()` method.
```php
    public function onMyEvent(MyEvent $event) : void {
        // some synchronous logic here
        $event->block();
        // some asynchronous logic here
        $event->release();
    }
```
If you accidentally forget to call the `release()` method, an exception will be thrown once the event instance is 
destroyed by the garbage collector.
