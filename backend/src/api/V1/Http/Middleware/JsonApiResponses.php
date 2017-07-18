<?php

namespace Api\V1\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class JsonApiResponses.
 *
 * @package Api\V1\Http\Middleware
 */
class JsonApiResponses
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        $this->assertRequest($request);

        $response = $next($request);

        $this->addHeaders($response);
        $this->setStatusCode($request, $response);

        return $response;
    }

    /**
     * Assert a request.
     *
     * @param Request $request
     */
    protected function assertRequest($request)
    {
        if (!$request->wantsJson()) {
            throw new HttpException(Response::HTTP_NOT_ACCEPTABLE);
        }
    }

    /**
     * Add default headers.
     *
     * @param Response $response
     * @return void
     */
    protected function addHeaders($response): void
    {
        $response->headers->set('Content-Type', 'application/json');
    }

    /**
     * Set status code for the response.
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    protected function setStatusCode($request, $response): void
    {
        if ($response->isSuccessful()) {
            switch ($request->getMethod()) {
                case Request::METHOD_POST:
                    $response->setStatusCode(Response::HTTP_CREATED);
                    break;
                case Request::METHOD_DELETE:
                    $response->setStatusCode(Response::HTTP_NO_CONTENT);
                    break;
            }
        }
    }
}
