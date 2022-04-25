# libAsyncEvent

libAsyncEvent is a simple implementation for creating asynchronous event execution for PocketMine-MP plugins.

## Why should I use this library?
I came to the idea for this library while implementing [libasynql](https://github.com/poggit/libasynql) and [await-generator](https://github.com/SOF3/await-generator) to my plugin [CPlot](https://github.com/ColinHDev/CPlot). I then realised how annoying it is, to deal with [PocketMine-MP's](https://github.com/pmmp/PocketMine-MP) events in this case, since it is not possible e.g. to check if a player is allowed to build in a certain area with an asynchronous-run query.

So when deciding to implement custom events into the plugin, I wanted to make it as developer-friendly as possible. So when someone decides to work with the events, they are not forced to directly decide, how to react (e.g. cancelling the event) and be allowed e.g. to run asynchronous queries to validate their decision.

## How to use this library in my plugin?
### How should my event class look like?
While you would normally make your event class extend [PocketMine-MP's event class](https://github.com/pmmp/PocketMine-MP/blob/stable/src/event/Event.php) or a child class of it, you replace it by this library's [AsyncEvent class](https://github.com/ColinHDev/libAsyncEvent/blob/main/src/ColinHDev/libAsyncEvent/AsyncEvent.php).
So instead of:
```php
use pocketmine\event\Event;

class MyEvent extends Event {}
```
You use:
```php
use ColinHDev\libAsyncEvent\AsyncEvent;

class MyEvent extends AsyncEvent {}
```
You then can proceed by adding a constructor, getters and setters or make the event cancelable.

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

## How to handle an event made with this library?
To register an event listener for an async event, you can do it the same way, you would normally do it for every other event listener:
```php
Server::getInstance()->getPluginManager();->registerEvents(new MyListener(), $this);
```
In this class, you as well create a method which accepts the event, you want to listen to, as a parameter:
```php
    public function onMyEvent(MyEvent $event) : void {
        // do something
    }
```
So basically there is no difference between a normal event listener and our event listener, except for one thing. 
To tell that you finished your checks, etc. and that the next event listener can be called you need to call the continue() method.
```php
    public function onMyEvent(MyEvent $event) : void {
        // some synchronous or asynchronous logic here
        $event->continue();
    }
```
If you accidentally forget this method call, an exception will be thrown once the event instance is destroyed by the garbage collector.