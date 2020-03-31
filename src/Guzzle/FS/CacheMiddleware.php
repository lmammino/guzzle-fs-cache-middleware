<?php

namespace LM\Guzzle\FS;

use Symfony\Component\Cache\Simple\FilesystemCache;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CacheItem {
    private $response;
    private $responseBody;

    public function __construct ($response) {
        $this->response = $response;
        $this->responseBody = $response->getBody()->getContents();
    }

    // since response are streams they can't be serialized
    // this uses the static response body which represents the flushed stream
    // and recontsructs a buffered stream from it
    public function getNormalizedResponse() {
        return $this->response->withBody(\GuzzleHttp\Psr7\stream_for($this->responseBody));
    }
}

class CacheMiddleware {
    public function __construct ($cacheDir, $namespace = 'default', $ttl = 60) {
       $this->cache = new FilesystemCache($namespace, $ttl, $cacheDir);
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use (&$handler) {
            // if it's not a get don't cache and return the response unmodified
            if (strtolower($request->getMethod()) != 'get') {
                return $handler($request, $options);
            }
            
            // calculates cache key
            $cacheKey = md5($request->getUri()->__toString().'___'.$request->getHeaderLine('authorization'));
            $cachedResponse = $this->cache->get($cacheKey);

            if ($cachedResponse) {
                // has cache return response from cache and add an extra header for debug
                return $cachedResponse->getNormalizedResponse()->withHeader('X-FS-Cache', 'HIT');
            }

            if (!$cachedResponse) {
                // make the request and cache
                return $handler($request, $options)->then(
                    function (ResponseInterface $response) use ($request, $cacheKey) {
                        if ($response->getStatusCode() > 399) {
                            // error response, do not cache, return as it is
                            return $response;
                        }
                        
                        // save the response in cache
                        $this->cache->set($cacheKey, new CacheItem($response));

                        // return response and add cache miss header
                        return $response->withHeader('X-FS-Cache', 'MISS');
                    }
                );
            }
        };
    }
}