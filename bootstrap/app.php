<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        /*
         * Railway / Reverse Proxy HTTPS support
         *
         * Railway terminates HTTPS before forwarding the request
         * to Laravel. Trust the proxy so Laravel knows the original
         * request was HTTPS.
         */
        $middleware->trustProxies(
            at: '*',
            headers: \Illuminate\Http\Middleware\TrustProxies::HEADER_X_FORWARDED_FOR
                | \Illuminate\Http\Middleware\TrustProxies::HEADER_X_FORWARDED_HOST
                | \Illuminate\Http\Middleware\TrustProxies::HEADER_X_FORWARDED_PORT
                | \Illuminate\Http\Middleware\TrustProxies::HEADER_X_FORWARDED_PROTO
                | \Illuminate\Http\Middleware\TrustProxies::HEADER_X_FORWARDED_AWS_ELB
        );

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();

return $app;
