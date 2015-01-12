<?php

namespace SimpleBus\Message\Bus\Middleware;

use SimpleBus\Message\Bus\MessageBus;
use SimpleBus\Message\Message;

class MessageBusSupportingMiddleware implements MessageBus
{
    /**
     * @var MessageBusMiddleware[]
     */
    private $middlewares = [];

    public function __construct(array $middlewares = [])
    {
        foreach ($middlewares as $middleware) {
            $this->addMiddleware($middleware);
        }
    }

    /**
     * Provide new middleware for this message bus. Should only be used at configuration time.
     *
     * @private
     * @param MessageBusMiddleware $middleware
     * @return void
     */
    public function addMiddleware(MessageBusMiddleware $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    public function handle(Message $message)
    {
        call_user_func($this->callableForNextMiddleware(0), $message);
    }

    private function callableForNextMiddleware($index)
    {
        if (!isset($this->middlewares[$index])) {
            return function() {};
        }

        $middleware = $this->middlewares[$index];

        return function(Message $message) use ($middleware, $index) {
            $middleware->handle($message, $this->callableForNextMiddleware($index + 1));
        };
    }
}
