<?php

declare(strict_types = 1);

namespace Sjdaws\LoyaltyCorpMailchimp;

/**
 * Handle api responses, deal with decoding json and the like
 */
class ApiResponse
{
    /**
     * The Response instance to get data from
     *
     * @var Response
     */
    private $response;

    /**
     * Create a new API response instance
     *
     * @param Response $response A response wrapper instance
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Get decoded contents of the response
     *
     * @return array
     */
    public function getContents() : array
    {
        return json_decode($this->getResponse()->getContents(), true);
    }

    /**
     * Get error(s) from the last response
     *
     * @return string
     */
    public function getErrorMessage() : string
    {
        // Get decoded contents and return the detail key
        $contents = $this->getContents();
        $error =  array_key_exists('detail', $contents) ? $contents['detail'] : '';

        // Add in field specific errors
        if (array_key_exists('errors', $contents)) {
            $error .= PHP_EOL . 'Field specific errors:';

            foreach ($contents['errors'] as $errorArray) {
                $error .= PHP_EOL . ' - ' . $errorArray['field'] . ': ' . $errorArray['message'];
            }
        }

        return trim($error);
    }

    /**
     * Get the Response wrapper
     *
     * @return Response Response class wrapper from the last response
     */
    public function getResponse() : Response
    {
        return $this->response;
    }

    /**
     * Get status code from the last response
     *
     * @return int
     */
    public function getStatusCode() : int
    {
        return $this->getResponse()->getStatusCode();
    }

    /**
     * Determine if the response was successful or not
     *
     * @return bool
     */
    public function hasErrors() : bool
    {
        return $this->getResponse()->hasErrors();
    }
}
