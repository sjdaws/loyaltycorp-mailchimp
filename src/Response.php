<?php

declare(strict_types = 1);

namespace Sjdaws\LoyaltyCorpMailchimp;

use GuzzleHttp\Psr7\Response as GuzzleResponse;

/**
 * Wrapper class for Guzzle response, maintain interface with system in case Guzzle is replaced in future
 */
class Response
{
    /**
     * The response received from guzzle
     *
     * @var \GuzzleHttp\Psr7\Response
     */
    private $response;

    /**
     * Create a new API instance
     *
     * @param \GuzzleHttp\Psr7\Response $response The response from a Guzzle request
     */
    public function __construct(GuzzleResponse $response)
    {
        $this->response = $response;
    }

    /**
     * Get response contents
     *
     * @return string
     */
    public function getContents() : string
    {
        return $this->response->getBody()->getContents();
    }

    /**
     * Get a single response header
     *
     * @param string $key The header key to retrieve
     *
     * @return array The array value if the key exists, otherwise an empty array
     */
    public function getHeader(string $key) : array
    {
        $headers = $this->getHeaders();

        // If we don't have a header array, return empty array
        if (!is_array($headers)) {
            return [];
        }

        foreach ($headers as $headerKey => $headerValue) {
            if (mb_strtolower($key) == mb_strtolower($headerKey)) {
                return $headerValue;
            }
        }

        // If we didn't find header, return empty array
        return [];
    }

    /**
     * Get response headers
     *
     * @return array
     */
    public function getHeaders() : array
    {
        return $this->response->getHeaders();
    }

    /**
     * Get response status code
     *
     * @return int
     */
    public function getStatusCode() : int
    {
        return $this->response->getStatusCode();
    }

    /**
     * Determine if the response has errors
     *
     * @return bool
     */
    public function hasErrors() : bool
    {
        return $this->getStatusCode() >= 400;
    }

    /**
     * Determine if the response has a specific header
     *
     * @param string $key The header key to check for
     *
     * @return bool
     */
    public function hasHeader(string $key) : bool
    {
        return $this->getHeader($key) !== [];
    }
}
