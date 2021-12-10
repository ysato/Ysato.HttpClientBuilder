<?php

declare(strict_types=1);

namespace Ysato\HttpClientBuilder;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;

class FakePlugin implements Plugin
{
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        // TODO: Implement handleRequest() method.
    }
}
