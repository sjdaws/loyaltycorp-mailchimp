<?php

declare(strict_types = 1);

namespace Sjdaws\Tests\LoyaltyCorpMailchimp;

use Sjdaws\LoyaltyCorpMailchimp\Client;
use Sjdaws\LoyaltyCorpMailchimp\Response;

/**
 * Tests for LoyaltyCorpMailchimp\Response
 */
class ResponseTest extends BaseTestCase
{
    /**
     * The mock server base url
     *
     * @var string
     */
    private $baseUrl;

    /**
     * Set up base url for test requests
     *
     * @return void
     */
    public function setUp()
    {
        $this->baseUrl = sprintf('http://%s:%d', SERVER_HOST, SERVER_PORT);
    }

    /**
     * Ensure we recieve a response class even if the request is unsuccessful
     *
     * @return void
     */
    public function testError()
    {
        $url = sprintf($this->baseUrl . '/500.php');

        $client = new Client;
        $response = $client->request('GET', $url);

        $this->assertTrue($response instanceof Response);
        $this->assertTrue($response->getStatusCode() === 500);
        $this->assertTrue($response->hasErrors() === true);
        $this->assertTrue($response->getContents() === 'VERY FAR FROM OK!');
    }

    /**
     * Ensure we can grab the headers that are sent with the request
     *
     * @return void
     */
    public function testHeaders()
    {
        $url = sprintf($this->baseUrl . '/headers.php');

        $client = new Client;
        $response = $client->request('GET', $url);

        $this->assertTrue($response instanceof Response);
        $this->assertTrue($response->getStatusCode() === 200);

        // Test content-type header exists and is what we set in docroot/headers.php
        $this->assertTrue($response->hasHeader('content-type') === true);
        $contentType = $response->getHeader('content-type');
        $this->assertTrue(count($contentType) > 0);
        $this->assertTrue(reset($contentType) === 'text/test;charset=UTF-8');

        // Test invalid header doesn't exist
        $this->assertFalse($response->hasHeader('invalid'));
        $invalid = $response->getHeader('invalid');
        $this->assertTrue(count($invalid) === 0);
        $this->assertTrue($invalid === []);
    }

    /**
     * Ensure we recieve a response class for a successful request
     *
     * @return void
     */
    public function testSuccess()
    {
        $url = sprintf($this->baseUrl . '/200.php');

        $client = new Client;
        $response = $client->request('GET', $url);

        $this->assertTrue($response instanceof Response);
        $this->assertTrue($response->getStatusCode() === 200);
        $this->assertTrue($response->hasErrors() === false);
        $this->assertTrue($response->getContents() === 'OK!');
    }
}
