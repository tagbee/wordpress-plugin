<?php

require_once('tagbee-auto-proposals-request.php');
require_once('tagbee-update-tags-request.php');
require_once('tagbee-rate-limiter-check-request.php');

class Tagbee_Client
{
    protected $apiKey;

    protected $secretKey;

    public function __construct($apiKey, $secretKey)
    {
        $this->apiKey = $apiKey;
        $this->secretKey = $secretKey;
    }

    /**
     * @return array|WP_Error
     */
    public function rateLimiterCheck(Tagbee_Rate_Limmiter_Check_Request $tagbeeRequest)
    {
        return wp_remote_get(
            'https://tagbee.co/api/rate-limiter/check',
            $this->requestArguments('GET', $tagbeeRequest)
        );
    }

    /**
     * @param string $tagbeeApiId
     * @param Tagbee_Update_Tags_Request $tagbeeRequest
     * @return array|WP_Error
     */
    public function putTags($tagbeeApiId, Tagbee_Update_Tags_Request $tagbeeRequest)
    {
        return wp_remote_post(
            'https://tagbee.co/api/article/' . $tagbeeApiId . '/tags',
            $this->requestArguments('PUT', $tagbeeRequest)
        );
    }

    /**
     * @param Tagbee_Auto_Proposals_Request $tagbeeRequest
     * @return array|WP_Error
     */
    public function postAutoProposals(Tagbee_Auto_Proposals_Request $tagbeeRequest)
    {
        return wp_remote_post(
            'https://tagbee.co/api/article/auto-proposals',
            $this->requestArguments('POST', $tagbeeRequest)
        );
    }

    /**
     * @param string $method
     * @param Tagbee_Request_Interface $tagbeeRequest
     * @return array
     */
    protected function requestArguments($method, Tagbee_Request_Interface $tagbeeRequest)
    {
        $jsonData = $tagbeeRequest->buildBody() ? json_encode($tagbeeRequest->buildBody()) : '';

        return array(
            'method' => $method,
            'blocking' => true,
            'headers' => array(
                'Accept'  => 'application/json',
                'Content-Type'  => 'application/json; charset=utf-8',
                'X-TagBee-PubKey' => $this->apiKey,
                'X-TagBee-Signature' => $this->signature($this->secretKey, (string) $jsonData),
            ),
            'body' => $jsonData,
            'timeout' => 20
        );
    }

    protected function signature($secretKey, $jsonData)
    {
        return base64_encode(hash_hmac('sha256', $jsonData, $secretKey, true));
    }
}
