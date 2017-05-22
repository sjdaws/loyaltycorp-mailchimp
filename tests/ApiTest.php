<?php

declare(strict_types = 1);

namespace Sjdaws\Tests\LoyaltyCorpMailchimp;

use Exception;
use RuntimeException;
use Sjdaws\LoyaltyCorpMailchimp\Api;
use Sjdaws\LoyaltyCorpMailchimp\ApiResponse;

/**
 * Tests for LoyaltyCorpMailchimp\Api
 */
class ApiTest extends BaseTestCase
{
    /**
     * Schema used for testing createRequestArrayFromSchema
     *
     * @var array
     */
    private $schema = [
        'array' => [
            'title' => 'Array',
            'type' => 'array',
            'schema' => [
                'int' => [
                    'title' => 'Integer',
                    'type' => 'int',
                ],
            ],
        ],
        'bool' => [
            'title' => 'Boolean',
            'type' => 'bool',
        ],
        'boolint' => [
            'title' => 'Integer as boolean',
            'type' => 'bool',
        ],
        'enum' => [
            'title' => 'Enum',
            'type' => 'enum',
            'values' => ['yes', 'no'],
        ],
        'keyvalue' => [
            'title' => 'Key/value pair',
            'type' => 'keyvalue',
        ],
        'numeric' => [
            'title' => 'Numeric',
            'type' => 'numeric',
        ],
        'null' => [
            'title' => 'Null',
            'type' => 'null',
        ],
        'object' => [
            'title' => 'Object',
            'type' => 'object',
            'schema' => [
                'email' => [
                    'title' => 'Email',
                    'type' => 'email',
                ],
            ],
        ],
        'string' => [
            'title' => 'String',
            'type' => 'string',
            'mandatory' => true,
        ],
    ];

    /**
     * Data used for testing createRequestArrayFromSchema
     *
     * @var array
     */
    private $data = [
        'array' => [
            ['int' => 3],
            ['int' => 4],
        ],
        'bool' => true,
        'boolint' => 0,
        'enum' => 'yes',
        'keyvalue' => [
            'key' => 'value',
        ],
        'numeric' => 1.23,
        'null' => null,
        'object' => [
            'email' => 'test@email.com',
        ],
        'string' => 'string',
    ];

    /**
     * Test constructor for invalid api key
     *
     * @return void
     */
    public function testInvalidApiKey()
    {
        $this->expectException(RuntimeException::class);

        // Remove api key
        putenv('MAILCHIMP_APIKEY=');

        // Should throw runtime exception
        $this->getMockForAbstractClass('Sjdaws\LoyaltyCorpMailchimp\Api');
    }

    /**
     * Test constructor with an api key that doesn't have a datacentre
     *
     * @return void
     */
    public function testInvalidApiKeyDc()
    {
        $this->expectException(RuntimeException::class);

        // Remove api key
        putenv('MAILCHIMP_APIKEY=1');

        // Should throw runtime exception
        $this->getMockForAbstractClass('Sjdaws\LoyaltyCorpMailchimp\Api');
    }

    /**
     * Test constructor with a valid api key
     *
     * @return void
     */
    public function testConstructor()
    {
        $api = $this->getMockForAbstractClass('Sjdaws\LoyaltyCorpMailchimp\Api');
        $this->assertTrue($api instanceof Api);
    }

    /**
     * Test createRequestArrayFromSchema
     *
     * @return void
     */
    public function testCreateRequestArrayFromSchema()
    {
        $api = $this->getMockForAbstractClass('Sjdaws\LoyaltyCorpMailchimp\Api');
        $this->callPrivateMethod($api, 'createRequestArrayFromSchema', [$this->data, $this->schema]);
    }

    /**
     * Test createRequestArrayFromSchema
     *
     * @return void
     */
    public function testCreateRequestArrayFromSchemaErrors()
    {
        $this->expectException(RuntimeException::class);

        // Create a purposely bad data array
        $data = $this->data;

        // Remove mandatory field
        unset($data['string']);

        // Create value mismatch
        $data['int'] = '';

        // Invalid array
        $data['object']['email'] = 'invalid';

        $api = $this->getMockForAbstractClass('Sjdaws\LoyaltyCorpMailchimp\Api');
        $this->callPrivateMethod($api, 'createRequestArrayFromSchema', [$data, $this->schema]);
    }

    /**
     * Test request
     *
     * @return void
     */
    public function testRequest()
    {
        $api = $this->getMockForAbstractClass('Sjdaws\LoyaltyCorpMailchimp\Api');
        $response = $this->callPrivateMethod($api, 'request', ['get', sprintf('http://%s:%d/200.php', SERVER_HOST, SERVER_PORT), ['headers' => ['Accept' => 'application/json']]]);

        $this->assertTrue($response instanceof ApiResponse);
    }

    /**
     * Test validateType
     *
     * @return void
     */
    public function testValidateType()
    {
        $api = $this->getMockForAbstractClass('Sjdaws\LoyaltyCorpMailchimp\Api');

        // Validate array passes and fails
        $this->assertTrue($this->callPrivateMethod($api, 'validateType', [[], 'array']));
        $this->assertFalse($this->callPrivateMethod($api, 'validateType', ['', 'array']));

        // Validate object is also treated as array
        $this->assertTrue($this->callPrivateMethod($api, 'validateType', [[], 'object']));

        // Validate boolean and integer as boolean
        $this->assertTrue($this->callPrivateMethod($api, 'validateType', [true, 'bool']));
        $this->assertTrue($this->callPrivateMethod($api, 'validateType', [0, 'bool']));
        $this->assertFalse($this->callPrivateMethod($api, 'validateType', ['', 'bool']));

        // Test int, numeric and null as above
        $this->assertTrue($this->callPrivateMethod($api, 'validateType', [1, 'int']));
        $this->assertFalse($this->callPrivateMethod($api, 'validateType', ['', 'int']));
        $this->assertTrue($this->callPrivateMethod($api, 'validateType', [1.2, 'numeric']));
        $this->assertFalse($this->callPrivateMethod($api, 'validateType', ['', 'numeric']));
        $this->assertTrue($this->callPrivateMethod($api, 'validateType', [null, 'null']));
        $this->assertFalse($this->callPrivateMethod($api, 'validateType', ['', 'null']));

        // Validate email/string passes as null and string
        $this->assertTrue($this->callPrivateMethod($api, 'validateType', [null, 'email']));
        $this->assertTrue($this->callPrivateMethod($api, 'validateType', ['scott@test.com', 'email']));
        $this->assertFalse($this->callPrivateMethod($api, 'validateType', [2, 'email']));
        $this->assertTrue($this->callPrivateMethod($api, 'validateType', [null, 'string']));
        $this->assertTrue($this->callPrivateMethod($api, 'validateType', ['scott', 'string']));
        $this->assertFalse($this->callPrivateMethod($api, 'validateType', [2, 'string']));

        // Finally test everything else passes
        $this->assertTrue($this->callPrivateMethod($api, 'validateType', [2, 'banana']));
    }

    /**
     * Test validateValue
     *
     * @return void
     */
    public function testValidateValue()
    {
        $api = $this->getMockForAbstractClass('Sjdaws\LoyaltyCorpMailchimp\Api');

        // A string should pass as there are no special checks
        $this->assertTrue($this->callPrivateMethod($api, 'validateValue', ['name', 'pineapple', ['type' => 'string']]));
    }

    /**
     * Test validateValue mandatory exception
     *
     * @return void
     */
    public function testValidateValueMandatory()
    {
        $this->expectException(Exception::class);

        $api = $this->getMockForAbstractClass('Sjdaws\LoyaltyCorpMailchimp\Api');

        // Should throw Exception
        $this->callPrivateMethod($api, 'validateValue', ['name', '', ['type' => 'string', 'mandatory' => true]]);
    }

    /**
     * Test validateValue type mismatch exception
     *
     * @return void
     */
    public function testValidateValueTypeMismatch()
    {
        $this->expectException(Exception::class);

        $api = $this->getMockForAbstractClass('Sjdaws\LoyaltyCorpMailchimp\Api');

        // Should throw Exception
        $this->callPrivateMethod($api, 'validateValue', ['name', 3, ['type' => 'string']]);
    }

    /**
     * Test validateValue invalid email exception
     *
     * @return void
     */
    public function testValidateValueInvalidEmail()
    {
        $this->expectException(Exception::class);

        $api = $this->getMockForAbstractClass('Sjdaws\LoyaltyCorpMailchimp\Api');

        // Should throw Exception
        $this->callPrivateMethod($api, 'validateValue', ['email', 'invalid', ['type' => 'email']]);
    }

    /**
     * Test validateValue enum no values exception
     *
     * @return void
     */
    public function testValidateValueEnumNoValues()
    {
        $this->expectException(Exception::class);

        $api = $this->getMockForAbstractClass('Sjdaws\LoyaltyCorpMailchimp\Api');

        // Should throw Exception
        $this->callPrivateMethod($api, 'validateValue', ['enum', 'invalid', ['type' => 'enum']]);
    }

    /**
     * Test validateValue enum invalid value exception
     *
     * @return void
     */
    public function testValidateValueEnumInvalid()
    {
        $this->expectException(Exception::class);

        $api = $this->getMockForAbstractClass('Sjdaws\LoyaltyCorpMailchimp\Api');

        // Should throw Exception
        $this->callPrivateMethod($api, 'validateValue', ['enum', 'invalid', ['type' => 'enum', 'values' => ['valid']]]);
    }

    /**
     * Test validateValue array no schema exception
     *
     * @return void
     */
    public function testValidateValueArrayNoSchema()
    {
        $this->expectException(Exception::class);

        $api = $this->getMockForAbstractClass('Sjdaws\LoyaltyCorpMailchimp\Api');

        // Should throw Exception
        $this->callPrivateMethod($api, 'validateValue', ['array', ['invalid'], ['type' => 'array']]);
    }
}
