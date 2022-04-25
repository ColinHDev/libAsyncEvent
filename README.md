# libAsyncEvent

libAsyncEvent is a simple implementation for creating asynchronous event execution for PocketMine-MP plugins.

## Why should I use this library?
I came to the idea for this library while implementing [libasynql](https://github.com/poggit/libasynql) and [await-generator](https://github.com/SOF3/await-generator) to my plugin [CPlot](https://github.com/ColinHDev/CPlot). I then realised how annoying it is, to deal with [PocketMine-MP's](https://github.com/pmmp/PocketMine-MP) events in this case, since it is not possible e.g. to check if a player is allowed to build in a certain area with an asynchronous-run query.

So when deciding to implement custom events into the plugin, I wanted to make it as developer-friendly as possible. So when someone decides to work with the events, they are not forced to directly decide, how to react (e.g. cancelling the event) and be allowed e.g. to run asynchronous queries to validate their decision.

## How to use this library?