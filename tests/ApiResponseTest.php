<?php

declare(strict_types = 1);

namespace Sjdaws\Tests\LoyaltyCorpMailchimp;

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Sjdaws\LoyaltyCorpMailchimp\ApiResponse;
use Sjdaws\LoyaltyCorpMailchimp\Response;

/**
 * Tests for LoyaltyCorpMailchimp\ApiResponse
 */
class ApiResponseTest extends BaseTestCase
{
    /**
     * Test error response
     *
     * @return void
     */
    public function testErrorResponse()
    {
        $body = [
            'detail' => 'Generic error message',
            'errors' => [
                ['field' => 'field_name', 'message' => 'field_name is mandatory'],
            ],
        ];

        $guzzle = new GuzzleResponse(500, [], json_encode($body));
        $response = new Response($guzzle);
        $apiResponse = new ApiResponse($response);

        $this->assertTrue($apiResponse->getStatusCode() == 500);
        $this->assertTrue($apiResponse->hasErrors());
        $this->assertTrue($apiResponse->getErrorMessage() == 'Generic error message' . PHP_EOL . 'Field specific errors:' . PHP_EOL . ' - field_name: field_name is mandatory');

    }

    /**
     * Test success response
     *
     * @return void
     */
    public function testSuccessfulResponse()
    {
        $body = ['name' => 'Scott', 'email' => 'scott@sjdaws.com'];

        $guzzle = new GuzzleResponse(200, [], json_encode($body));
        $response = new Response($guzzle);
        $apiResponse = new ApiResponse($response);

        $this->assertTrue($apiResponse->getStatusCode() == 200);
        $this->assertFalse($apiResponse->hasErrors());
        $this->assertTrue($apiResponse->getContents() == $body);
    }
}
