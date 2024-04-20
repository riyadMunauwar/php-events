<?php

use Closure;

class Dispatcher
{
    protected $listeners = [];
    protected $wildCardListeners = [];

    public function listen($events, $listener)
    {
        foreach ((array) $events as $event) {
            if (str_contains($event, '*')) {
                $this->setupWildcardListenerForEvent($event, $listener);
            } else {
                $this->listeners[$event][] = $this->makeListener($listener);
            }
        }
    }

    public function makeListener($listener)
    {
        if (is_string($listener)) {
            $listener = $this->createClassListener($listener);
        }

        return $listener;
    }

    protected function createClassListener($listener)
    {
        $app = $this->container;

        return function () use ($app, $listener) {
            $segments = explode('@', $listener);

            $instance = $app->make($segments[0]);

            $method = $segments[1] ?? 'handle';

            return $instance->$method(...func_get_args());
        };
    }

    public function hasListeners($eventName)
    {
        return isset($this->listeners[$eventName]) || isset($this->wildCardListeners[$eventName]);
    }

    public function fire($event, $payload = [])
    {
        $responses = [];

        $this->fireListeners($event, $payload, $responses);

        return $responses;
    }

    protected function fireListeners($event, $payload, &$responses)
    {
        if (isset($this->wildCardListeners[$event])) {
            $this->fireWildcardListeners($event, $payload, $responses);
        }

        if (isset($this->listeners[$event])) {
            $this->fireRegularListeners($event, $payload, $responses);
        }
    }

    protected function fireRegularListeners($event, $payload, &$responses)
    {
        foreach ($this->listeners[$event] as $listener) {
            $responses[] = call_user_func_array($listener, $payload);
        }
    }

    protected function fireWildcardListeners($event, $payload, &$responses)
    {
        foreach ($this->wildCardListeners[$event] as $listener) {
            $responses[] = call_user_func_array($listener, $payload);
        }
    }

    protected function setupWildcardListenerForEvent($event, $listener)
    {
        $this->wildCardListeners[$event][] = $this->makeListener($listener);
    }
}