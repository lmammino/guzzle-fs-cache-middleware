# guzzle-fs-cache-middleware

A minimalist and opinionated Cache Middleware for Guzzle that uses the FileSystem as backend (compatible with PHP 5.6+).

## Install

Using composer:

```bash
composer require lmammino/guzzle-fs-cache-middleware
```

## Usage

Create an instance of `LM\Guzzle\FS\CacheMiddleware` and add it to your middleware stack:

```php
require_once __DIR__.'/vendor/autoload.php';

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Client;
use LM\Guzzle\FS\CacheMiddleware;

$cacheDir = __DIR__.'/cache/';
$namespace = 'api_client';
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
```

`CacheMiddleware` accepts a cache directory, an optional namespace (defaults to `default`) and an optiona ttl in seconds (defaults to `60`).

**Note**: this middleware will always respect the TTL you provide and will ignore any HTTP cache header returned as response. This is by design. Use this middleware only if you want to enforce cache or for micro-caching scenarios (e.g. very expensive and frequent API calls).

## Contributing

Everyone is very welcome to contribute to this project.
You can contribute just by submitting bugs or suggesting improvements by
[opening an issue on GitHub](https://github.com/lmammino/guzzle-fs-cache-middleware/issues).


## License

Licensed under [MIT License](LICENSE). © Luciano Mammino.
