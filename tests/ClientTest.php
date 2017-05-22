<?php

declare(strict_types = 1);

namespace Sjdaws\Tests\LoyaltyCorpMailchimp;

use Sjdaws\LoyaltyCorpMailchimp\Client;
use Sjdaws\LoyaltyCorpMailchimp\Response;

/**
 * Tests for LoyaltyCorpMailchimp\Client
 */
class ClientTest extends BaseTestCase
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
    }
}
