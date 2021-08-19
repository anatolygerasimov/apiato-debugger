<?php

namespace App\Containers\AppSection\Debugger\Values;

use App\Ship\Parents\Values\Value;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Output extends Value
{
    public string $output = '';

    protected int $responseDataCut;

    protected int $tokenDataCut;

    private Agent $agent;

    public function __construct(private Request $request, private Response | JsonResponse $response)
    {
        $this->agent           = app(Agent::class);
        $this->responseDataCut = config('debugger.requests.response_show_first');
        $this->tokenDataCut    = config('debugger.requests.token_show_first');
    }

    public function get(): string
    {
        return $this->output;
    }

    public function clear(): void
    {
        $this->set('');
    }

    public function addHeader(string $name): void
    {
        $this->append("{$name}: \n");
    }

    /**
     * Add line to indicate new request.
     */
    public function newRequest(): void
    {
        $this->append('----------------- NEW REQUEST -----------------');
    }

    /**
     * Add empty line.
     */
    public function spaceLine(): void
    {
        $this->append("\n \n");
    }

    public function endpoint(): void
    {
        $this->append(' * Endpoint: ' . $this->request->fullUrl() . "\n");
        $this->append(' * Method: ' . $this->request->getMethod() . "\n");
    }

    public function version(): void
    {
        if (method_exists($this->request, 'version')) {
            $this->append(' * Version: ' . $this->request->version() . "\n");
        }
    }

    public function ip(): void
    {
        $ip = $this->request->ip() ?? '';
        $this->append(' * IP: ' . $ip . ' (Port: ' . $this->request->getPort() . ") \n");
    }

    public function format(): void
    {
        $this->append(' * Format: ' . $this->request->format() . "\n");
    }

    public function userInfo(): void
    {
        // Auth Header
        $authHeader = $this->request->header('Authorization');
        // User
        $user = $this->request->user() ? 'ID: ' . $this->request->user()->id . ' (Name: ' . $this->request->user()->name . ')' : 'N/A';
        // Browser
        $browser = $this->agent->browser();
        $browser = is_string($browser) ? (string)$browser : 'N/A';

        $cutTokenString = is_string($authHeader) ? substr($authHeader, 0, $this->tokenDataCut) : '';

        $this->append(' * Access Token: ' . $cutTokenString . (is_null($authHeader) ? 'N/A' : '...') . "\n");
        $this->append(' * User: ' . $user . "\n");
        $this->append(' * Device: ' . $this->agent->device() . ' (Platform: ' . $this->agent->platform() . ") \n");
        $this->append(' * Browser: ' . $browser . ' (Version: ' . $this->agent->version($browser) . ") \n");
        $this->append(' * Languages: ' . implode(', ', $this->agent->languages()) . "\n");
    }

    public function requestData(): void
    {
        try {
            // Request Data
            $requestData = $this->request->all() ? http_build_query($this->request->all(), '', ' + ') : 'N/A';
        } catch (Throwable $throwable) {
            info($throwable->getMessage());
            $requestData = $throwable->getMessage();
        }

        $this->append(' * ' . $requestData . "\n");
    }

    public function responseData(): void
    {
        // Response Data
        $responseContent = method_exists($this->response, 'content') ? $this->response->content() : 'N/A';

        $this->append(' * ' . substr($responseContent, 0, $this->responseDataCut) . '...' . "\n");
    }

    protected function set(string $text): string
    {
        return $this->output = $text;
    }

    private function append(string $output): string
    {
        return $this->output .= $output;
    }
}
