<?php

declare(strict_types = 1);

namespace Sjdaws\LoyaltyCorpMailchimp;

use GuzzleHttp\Client as GuzzleRequest;
use GuzzleHttp\Exception\BadResponseException;

/**
 * Wrapper class for Guzzle client, maintain interface with system in case Guzzle is replaced in future
 */
class Client
{
    /**
     * Process a request to an endpoint via guzzle
     *
     * @param string $method     The method to use for the request
     * @param string $url        The url to connect to
     * @param array  $parameters Additional parameters to send with the request
     *
     * @return Response A Response wrapper instance
     */
    public function request(string $method, string $url, array $parameters = []) : Response
    {
        try {
            $response = (new GuzzleRequest)->request($method, $url, $parameters);
            return new Response($response);
        } catch (BadResponseException $e) {
            // Catch 4/500 errors so response is always standardised
            return new Response($e->getResponse());
        }
    }
}
