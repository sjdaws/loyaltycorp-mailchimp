<?php

declare(strict_types = 1);

namespace Sjdaws\LoyaltyCorpMailchimp;

use Exception;
use RuntimeException;

/**
 * Handle common functionality for API requests and responses
 */
abstract class Api
{
    /**
     * The API key used to connect to mailchimp
     *
     * @var string
     */
    private $apiKey;

    /**
     * The base endpoint used to connect to the mailchimp api
     *
     * @var string
     */
    protected $baseUrl = 'https://<dc>.api.mailchimp.com/3.0';

    /**
     * The endpoint for the current API request
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Create a new API instance
     *
     * @param array $data The data received from a call to an endpoint
     *
     * @throws RuntimeException If mailchimp credentials aren't set in env
     */
    public function __construct(array $data = [])
    {
        // Ensure API key is set
        if (!getenv('MAILCHIMP_APIKEY')) {
            throw new RuntimeException('MAILCHIMP_APIKEY must be set in env');
        }

        if (strpos(getenv('MAILCHIMP_APIKEY'), '-') === false) {
            throw new RuntimeException('MAILCHIMP_APIKEY is invalid, see https://admin.mailchimp.com/account/api/');
        }

        // Get dc from apikey
        list(,$dc) = explode('-', getenv('MAILCHIMP_APIKEY'));

        // Set class variables
        $this->apiKey = getenv('MAILCHIMP_APIKEY');
        $this->baseUrl = str_replace('<dc>', $dc, $this->baseUrl);
    }

    /**
     * Create a request array from a data array and a schema array
     *
     * @param array  $data   The used data to create the request
     * @param array  $schema The schema array used to sort the data
     * @param string $prefix The prefix to add before the field name when friendly name isn't used
     *
     * @return array
     *
     * @throws Exception If validation fails
     */
    protected function createRequestArrayFromSchema(array $data, array $schema, string $prefix = '') : array
    {
        $errors = $response = [];

        // Determine prefix for errors
        $prefix = $prefix ? $prefix . '.' : '';

        foreach ($schema as $key => $requirements) {
            $fieldName = $requirements['title'] ?? $prefix . $key;

            // If we don't have a matching key in data, skip
            if (!array_key_exists($key, $data)) {
                // If this field is required, track error
                if (array_key_exists('mandatory', $requirements) && $requirements['mandatory'] === true) {
                    $errors[] = $fieldName . ' is mandatory';
                }

                continue;
            }

            // Validate the value
            try {
                $this->validateValue($fieldName, $data[$key], $requirements);
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
                continue;
            }

            // If we have an array, go deeper
            if (is_array($data[$key])) {
                try {
                    // If type is an array, loop through values
                    if ($requirements['type'] == 'array') {
                        $responses = [];

                        foreach ($data[$key] as $value) {
                            $responses[] = $this->createRequestArrayFromSchema($value, $requirements['schema'], $key);
                        }

                        $response[$key] = $responses;
                        continue;
                    }

                    // If we have a key/value it's been unserialised into an array so add and move on
                    if ($requirements['type'] == 'keyvalue') {
                        $response[$key] = $data[$key];
                        continue;
                    }

                    $response[$key] = $this->createRequestArrayFromSchema($data[$key], $requirements['schema'], $key);
                } catch (Exception $e) {
                    $errors[] = trim($e->getMessage());
                }

                continue;
            }

            // Cast integers to booleans and nulls to strings if required
            if ($requirements['type'] == 'bool' && is_int($data[$key])) {
                $data[$key] = (bool)$data[$key];
            }
            if (is_null($data[$key])) {
                $data[$key] = '';
            }

            $response[$key] = $data[$key];
        }

        // If we have errors throw an exception with them concatenated together
        if (count($errors)) {
            throw new RuntimeException(PHP_EOL . implode(PHP_EOL, $errors));
        }

        return $response;
    }

    /**
     * Process a request to an endpoint via guzzle
     *
     * @param string $method     The method to use for the request
     * @param string $url        The url to connect to
     * @param array  $parameters Additional parameters to send with the request
     *
     * @return ApiResponse ApiResponse instance
     */
    protected function request(string $method, string $url, array $parameters = []) : ApiResponse
    {
        // Add in common headers
        $parameters = array_merge_recursive(
            $options = [
                'auth' => [null, $this->apiKey],
                'headers' => [
                    ['Accept' => 'application/vnd.api+json'],
                    ['Content-Type' => 'application/vnd.api+json'],
                ],
            ],
            $parameters
        );

        $response = (new Client)->request($method, $url, $parameters);

        // Return ApiResponse
        return new ApiResponse($response);
    }

    /**
     * Validate a variable type
     *
     * @param mixed $value The value to validate
     * @param string $type The type to validate against
     *
     * @return bool
     */
    private function validateType($value, string $type) : bool
    {
        switch ($type) {
            case 'array':
            case 'object':
                return is_array($value);
                break;

            case 'bool':
                // If we have an integer, case it
                if (is_int($value)) {
                    $value = (bool)$value;
                }

                // There is intentionally no break here

            case 'int':
            case 'numeric':
            case 'null':
                $method = 'is_' . $type;
                return $method($value);
                break;

            case 'email':
            case 'string':
                return is_null($value) || is_string($value);
                break;
        }

        // If we haven't already returned, it passes
        return true;
    }

    /**
     * Validate a variable value
     *
     * @param string $fieldName    The field's friendly name
     * @param mixed  $value        The value to validate
     * @param array  $requirements The requirements to validate against
     *
     * @return bool
     *
     * @throws Exception If variable is invalid
     */
    private function validateValue(string $fieldName, $value, array $requirements) : bool
    {
        // Get expected type
        $type = mb_strtolower($requirements['type']) ?? '';

        // Validate value
        if (empty($value) && array_key_exists('mandatory', $requirements) && $requirements['mandatory'] === true) {
            throw new Exception($fieldName . ' is mandatory');
        }

        // Validate type
        if (!$this->validateType($value, $type)) {
            throw new Exception($fieldName . ' is invalid type, expected ' . $type . ' got ' . gettype($value));
        }

        // Validate email addresses
        if ($type == 'email' && ($value && !filter_var($value, FILTER_VALIDATE_EMAIL))) {
            throw new Exception($value . ' is invalid, expected valid email address got ' . $value);
        }

        // Validate enums
        if ($type == 'enum') {
            if (!array_key_exists('values', $requirements)) {
                throw new Exception($fieldName . ' is invalid, schema is missing expected values');
            } elseif (!in_array($value, $requirements['values'])) {
                throw new Exception($fieldName . ' is invalid, expected one of [' . implode(', ', $requirements['values']) . '] got ' . $value);
            }
        }

        // Validate arrays and objects
        if (($type == 'array' || $type == 'object') && (!array_key_exists('schema', $requirements) || !is_array($requirements['schema']))) {
            throw new Exception($fieldName . ' is invalid, schema is missing sub-schema');
        }

        return true;
    }
}
