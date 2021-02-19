<?php

namespace Omnipay\GoPay\Message;

use Omnipay\Common\Message\AbstractRequest;

class AccessTokenRequest extends AbstractRequest
{
    private $data = [
        'scope' => 'payment-create',
        'grant_type' => 'client_credentials',
    ];

    public function setClientId($clientId)
    {
        $this->setParameter('clientId', $clientId);
    }

    public function setClientSecret($clientSecret)
    {
        $this->setParameter('clientSecret', $clientSecret);
    }

    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     * @return AccessTokenResponse
     */
    public function sendData($data)
    {
        $credentials = $this->getParameter('clientId') . ':' . $this->getParameter('clientSecret');
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic ' . base64_encode($credentials),
        ];

        $httpResponse = $this->httpClient->request(
            'POST',
            $this->getParameter('apiUrl') . '/api/oauth2/token',
            $headers,
            http_build_query($data)
        );

        $tokenData = json_decode($httpResponse->getBody()->getContents(), true);

        $response = new AccessTokenResponse($this, $tokenData);
        return $response;
    }

    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $apiUrl
     */
    public function setApiUrl($apiUrl)
    {
        $this->setParameter('apiUrl', $apiUrl);
    }

    /**
     * @param string $scope
     */
    public function setScope($scope)
    {
        $this->data['scope'] = $scope;
    }

    /**
     * @param string $grantType
     */
    public function setGrantType($grantType)
    {
        $this->data['grant_type'] = $grantType;
    }
}
