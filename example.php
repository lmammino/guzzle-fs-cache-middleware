<?php

require_once __DIR__.'/vendor/autoload.php';

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Client;
use LM\Guzzle\FS\CacheMiddleware;

$cacheDir = __DIR__.'/cache/';
$namespace = 'launch_darkly';
$ttl = 60;
$cacheMiddleware = new CacheMiddleware($cacheDir, $namespace, $ttl);

$stack = new HandlerStack();
$stack->setHandler(new CurlHandler());
$stack->push($cacheMiddleware);
$client = new Client(['handler' => $stack]);

$res = $client->request('GET', $argv[1]);
$headers = $res->getHeaders();
foreach ($headers as  $k => $h) {
    foreach ($h as $v) {
        echo $k . ": " . $v . "\n";
    }
}
echo "\n\n" . $res->getBody()->__toString() . "\n\n";
