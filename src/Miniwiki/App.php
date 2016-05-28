<?php
namespace Miniwiki;

use Psr\Http\Message\ServerRequestInterface as RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Slim\App as SlimApp;

class App extends SlimApp
{
    public function getJson($pattern, callable $callable, callable $nonJsonCallable = null)
    {
        $this->mapJson('GET', $pattern, $callable, $nonJsonCallable);
    }

    public function putJson($pattern, callable $callable)
    {
        $this->mapJson('PUT', $pattern, $callable);
    }

    public function deleteJson($pattern, callable $callable)
    {
        $this->mapJson('DELETE', $pattern, $callable);
    }

    public function postJson($pattern, callable $callable, callable $nonJsonCallable = null)
    {
        $this->mapJson('POST', $pattern, $callable);
    }

    public function patchJson($pattern, callable $callable)
    {
        $this->mapJson('PATCH', $pattern, $callable);
    }

    public function getClientIpAddress(RequestInterface $request)
    {
        return $request->getServerParams()['REMOTE_ADDR']; // FIXME not always going to be accurate
    }

    public function encodeJson($data, $options = null)
    {
        if ($options === null) {
            $options = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        }
        $json = json_encode($data, $options);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to encode JSON');
        }

        return $json;
    }

    private function mapJson($method, $pattern, callable $callable, callable $nonJsonCallable = null)
    {
        $app = $this;

        if ($callable instanceof \Closure) {
            $callable = $callable->bindTo($this->getContainer());
        }
        if ($nonJsonCallable instanceof \Closure) {
            $nonJsonCallable = $nonJsonCallable->bindTo($this->getContainer());
        }

        $this->map([$method], $pattern, function (RequestInterface $request, ResponseInterface $response, $args) use ($app, $callable, $method, $nonJsonCallable, $pattern) {
            if (!$app->shouldUseJson($request)) {
                return $nonJsonCallable ? $nonJsonCallable($request, $response, $args) : $response->withStatus(404);
            }

            return $callable($request, $response, $args);
        });
    }

    private function shouldUseJson(RequestInterface $request)
    {
        $contentTypeInput = trim($request->getHeaderLine('Content-Type'));
        $accepts = trim($request->getHeaderLine('Accept'));

        return strpos($contentTypeInput, 'application/json') === 0 ||
               strpos($accepts, 'application/json') === 0;
    }
}
