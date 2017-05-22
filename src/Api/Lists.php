<?php

declare(strict_types = 1);

namespace Sjdaws\LoyaltyCorpMailchimp\Api;

use Sjdaws\LoyaltyCorpMailchimp\Api;
use Sjdaws\LoyaltyCorpMailchimp\ApiResponse;

/**
 * A class for interacting with lists on mailchimp
 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/
 */
class Lists extends Api
{
    /**
     * The endpoint for managing lists
     *
     * @var string
     */
    protected $endpoint = 'lists';

    /**
     * Data definition based on Mailchimp list, used for validation and when adding or updating a list
     *
     * @var array
     */
    private $listSchema = [
        'name' => [
            'title' => 'List name',
            'type' => 'string',
            'mandatory' => true,
        ],
        'contact' => [
            'title' => 'List contact',
            'type' => 'object',
            'schema' => [
                'company' => [
                    'title' => 'Company name',
                    'type' => 'string',
                    'mandatory' => true,
                ],
                'address1' => [
                    'title' => 'Address 1',
                    'type' => 'string',
                    'mandatory' => true,
                ],
                'address2' => [
                    'title' => 'Address 2',
                    'type' => 'string',
                ],
                'city' => [
                    'title' => 'City',
                    'type' => 'string',
                    'mandatory' => true,
                ],
                'state' => [
                    'title' => 'State',
                    'type' => 'string',
                    'mandatory' => true,
                ],
                'zip' => [
                    'title' => 'Post code',
                    'type' => 'string',
                    'mandatory' => true,
                ],
                'country' => [
                    'title' => 'Country',
                    'type' => 'string',
                    'mandatory' => true,
                ],
                'phone' => [
                    'title' => 'Phone number',
                    'type' => 'string',
                ],
            ],
        ],
        'permission_reminder' => [
            'title' => 'Permission reminder',
            'type' => 'string',
            'mandatory' => true,
        ],
        'use_archive_bar' => [
            'title' => 'Use archive bar',
            'type' => 'bool',
        ],
        'campaign_defaults' => [
            'title' => 'Campaign defaults',
            'type' => 'object',
            'schema' => [
                'from_name' => [
                    'title' => "Sender's name",
                    'type' => 'string',
                    'mandatory' => true,
                ],
                'from_email' => [
                    'title' => "Sender's email address",
                    'type' => 'email',
                    'mandatory' => true,
                ],
                'subject' => [
                    'title' => 'Subject',
                    'type' => 'string',
                    'mandatory' => true,
                ],
                'language' => [
                    'title' => 'Language',
                    'type' => 'string',
                    'mandatory' => true,
                ],
            ],
        ],
        'notify_on_subscribe' => [
            'title' => 'Notify on subscribe',
            'type' => 'email',
        ],
        'notify_on_unsubscribe' => [
            'title' => 'Notify on unsubscribe',
            'type' => 'email',
        ],
        'email_type_option' => [
            'title' => 'Email type option',
            'type' => 'bool',
        ],
        'visibility' => [
            'title' => 'Visibility',
            'type' => 'enum',
            'values' => [
                'pub',
                'prv',
            ],
        ],
    ];

    /**
     * Data definition for bulk subscriptions, used for validation and when adding or updating a list
     *
     * @var array
     */
    private $bulkSubscribeSchema = [
        'members' => [
            'type' => 'array',
            'schema' => [
                'email_address' => [
                    'title' => 'Email address',
                    'type' => 'email',
                    'mandatory' => true,
                ],
                'status' => [
                    'title' => 'Status',
                    'type' => 'enum',
                    'values' => [
                        'subscribed',
                        'unsubscribed',
                        'cleaned',
                        'pending',
                    ],
                ],
            ],
        ],
        'update_existing' => [
            'title' => 'Update existing members',
            'type' => 'bool',
        ],
    ];

    /**
     * Create a new list
     *
     * @param array $data The data to use to create the list
     *
     * @return ApiResponse The response from the api
     */
    public function add(array $data) : ApiResponse
    {
        return $this->request('POST', $this->getUrl(), ['json' => $this->createRequestArrayFromSchema($data, $this->listSchema)]);
    }

    /**
     * Perform a bulk subscribe/unsubscribe
     *
     * @param array $data The data to send to the api
     *
     * @return ApiResponse The response from the api
     */
    public function bulk(string $id, array $data) : ApiResponse
    {
        return $this->request('POST', $this->getUrl($id), ['json' => $this->createRequestArrayFromSchema($data, $this->bulkSubscribeSchema)]);
    }

    /**
     * Delete a list from Mailchimp
     *
     * @param string $id The list to delete
     *
     * @return ApiResponse The response from the api
     */
    public function delete(string $id) : ApiResponse
    {
        return $this->request('DELETE', $this->getUrl($id));
    }

    /**
     * Get all lists or a single list if id is provided
     *
     * @param string $id The list to retrieve
     *
     * @return ApiResponse The response from the api
     */
    public function get(string $id) : ApiResponse
    {
        return $this->request('GET', $this->getUrl($id));
    }

    /**
     * Get all lists
     *
     * @param array $parameters Parameters to send with the request, e.g. pagination
     *
     * @return ApiResponse The reponse from the api
     */
    public function getAll(array $parameters = []) : ApiResponse
    {
        $url = $this->getUrl();

        // Create query string from parameters
        if (count($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }

        return $this->request('GET', $url);
    }

    /**
     * Get the full url to this endpoint
     *
     * @param string $id The id to append to the endpoint
     *
     * @return string The full url to the endpoint
     */
    protected function getUrl(string $id = '') : string
    {
        // Append id to the endpoint if we have one
        $endpoint = $id ? $this->endpoint . '/' . $id : $this->endpoint;

        return sprintf('%s/%s', $this->baseUrl, ltrim($endpoint, '/'));
    }

    /**
     * Update a list
     *
     * @param string $id   The id of the list to upadte
     * @param array  $data The data to update in the list
     *
     * @return ApiResponse The response from the api
     */
    public function update(string $id, array $data) : ApiResponse
    {
        return $this->request('PATCH', $this->getUrl($id), ['json' => $this->createRequestArrayFromSchema($data, $this->listSchema)]);
    }
}
