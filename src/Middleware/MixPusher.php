<?php

namespace Eolme\MixPusher\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Cache;

class MixPusher
{
    /**
     * Handle an incoming request
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var Response $response */
        $response = $next($request);

        if ($this->shouldProcess($response)) {
            $url = $request->getHttpHost() . $request->getBaseUrl() . $request->getPathInfo();
            $cache = Cache::tags('mix-pusher');
            foreach (config('mix-pusher.routes') as $route => $res) {
                if (strpos($url, $route) === 0) {
                    foreach ($res as $name) {
                        if ($cache->has($name)) {
                            $link = $cache->get($name);
                            if ($this->endWith($name, 'js')) {
                                $this->addLinkHeader($response, "<{$link}>; rel=preload; as=script");
                                continue;
                            }
                            if ($this->endWith($name, 'css')) {
                                $this->addLinkHeader($response, "<{$link}>; rel=preload; as=style");
                            }
                        }
                    }
                }
            };
        }

        return $response;
    }

    /**
     * Determines whether a string ends with the characters of a specified string
     * 
     * @param string $haystack
     * @param string $needle
     * 
     * @return bool
     */
    private function endWith($haystack, $needle): bool
    {
        return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }

    /**
     * Check if the content type header is html
     *
     * @param Response $response
     *
     * @return bool
     */
    private function isHtml(Response $response): bool
    {
        return 0 === strpos($response->headers->get('Content-Type'), 'text/html');
    }

    /**
     * Check if the response should be processed
     *
     * @param mixed $response
     *
     * @return bool
     */
    private function shouldProcess(Response $response): bool
    {
        if (!($response instanceof Response)) {
            return false;
        }

        if ($response instanceof BinaryFileResponse) {
            return false;
        }

        if ($response instanceof StreamedResponse) {
            return false;
        }

        return $this->isHtml($response);
    }

    /**
     * Add Link Header
     *
     * @param Response $response
     * @param string $link
     */
    private function addLinkHeader(Response $response, $link): void
    {
        if ($response->headers->get('Link')) {
            $link = $response->headers->get('Link') . ',' . $link;
        }
        $response->header('Link', $link);
    }
}
