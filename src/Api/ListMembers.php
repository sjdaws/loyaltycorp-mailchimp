<?php

declare(strict_types = 1);

namespace Sjdaws\LoyaltyCorpMailchimp\Api;

use Sjdaws\LoyaltyCorpMailchimp\Api;
use Sjdaws\LoyaltyCorpMailchimp\ApiResponse;

/**
 * A class for interacting with list members on mailchimp
 * @link http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/
 */
class ListMembers extends Api
{
    /**
     * The endpoint for managing list members
     *
     * @var string
     */
    protected $endpoint = 'lists/{listId}/members';

    /**
     * Data definition based on Mailchimp list, used for validation and when adding or updating a list
     *
     * @var array
     */
    private $memberSchema = [
        'email_address' => [
            'title' => 'Email address',
            'type' => 'email',
            'mandatory' => true,
        ],
        'email_type' => [
            'title' => 'Email type',
            'type' => 'string',
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
        'merge_fields' => [
            'title' => 'Member merge fields',
            'type' => 'keyvalue',
        ],
        'interests' => [
            'title' => 'Member Interests',
            'type' => 'keyvalue',
        ],
        'language' => [
            'title' => 'Member language',
            'type' => 'string',
        ],
        'vip' => [
            'title' => 'VIP',
            'type' => 'bool',
        ],
        'location' => [
            'title' => 'Location',
            'type' => 'object',
            'schema' => [
                'longitude' => [
                    'title' => 'Longitude',
                    'type' => 'numeric',
                ],
                'latitude' => [
                    'title' => 'Latitude',
                    'type' => 'numeric',
                ],
            ],
        ],
        'ip_signup' => [
            'title' => 'Signup IP',
            'type' => 'string',
        ],
        'timestamp_signup' => [
            'title' => 'Signup timestamp',
            'type' => 'string',
        ],
        'ip_opt' => [
            'title' => 'Opt-in IP',
            'type' => 'string',
        ],
        'timestamp_opt' => [
            'title' => 'Opt-in timestamp',
            'type' => 'string',
        ],
    ];

    /**
     * Create a new member
     *
     * @param string $listId The id of the list to add this member to
     * @param array  $data   The data to use to create the list
     *
     * @return ApiResponse The response from the api
     */
    public function add(string $listId, array $data) : ApiResponse
    {
        return $this->request('POST', $this->getUrl($listId), ['json' => $this->createRequestArrayFromSchema($data, $this->memberSchema)]);
    }

    /**
     * Delete a member from a list
     *
     * @param string $listId The list to delete from
     * @param string $email  The members email address
     *
     * @return ApiResponse The response from the api
     */
    public function delete(string $listId, string $email) : ApiResponse
    {
        return $this->request('DELETE', $this->getUrl($listId, $email));
    }

    /**
     * Get a single member
     *
     * @param string $listId The list to fetch from
     * @param string $email  The members email address
     *
     * @return ApiResponse The response from the api
     */
    public function get(string $listId, string $email) : ApiResponse
    {
        return $this->request('GET', $this->getUrl($listId, $email));
    }

    /**
     * Get all list members
     *
     * @param string $listId The list to fetch from
     * @param array $parameters Parameters to send with the request, e.g. pagination
     *
     * @return ApiResponse The reponse from the api
     */
    public function getAll(string $listId, array $parameters = []) : ApiResponse
    {
        $url = $this->getUrl($listId);

        // Create query string from parameters
        if (count($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }

        return $this->request('GET', $url);
    }

    /**
     * Override the base getUrl method to include list id and subscriber hash
     *
     * @param string $listId The list for this member
     * @param string $email  The member email address
     *
     * @return string
     */
    private function getUrl(string $listId, string $email = '') : string
    {
        // Append hash to the endpoint if we have one
        $endpoint = $email ? $this->endpoint . '/' . md5($email) : $this->endpoint;

        // Replace list id
        $endpoint = str_replace('{listId}', $listId, $endpoint);

        return sprintf('%s/%s', $this->baseUrl, ltrim($endpoint, '/'));
    }

    /**
     * Update a list member
     *
     * @param string $listId The id of the list this member is associated with
     * @param string $email  The members email address
     * @param array  $data The data to update on the member
     *
     * @return ApiResponse The response from the api
     */
    public function update(string $listId, string $email, array $data) : ApiResponse
    {
        return $this->request('PATCH', $this->getUrl($listId, $email), ['json' => $this->createRequestArrayFromSchema($data, $this->memberSchema)]);
    }
}
