<?php

namespace Omnipay\GoPay\Message;

use Omnipay\Common\Message\AbstractRequest;

/**
 * @see https://doc.gopay.com/en/#charge-of-pre-authorization
 */
class CaptureAuthorizationRequest extends AbstractRequest
{

    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->getParameter('transactionReference');
    }

    /**
     * Get the transaction reference which was used to create authorization
     *
     * @return mixed
     */
    public function getTransactionReference()
    {
        return $this->getParameter('transactionReference');
    }

    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     * @return CaptureAuthorizationResponse
     */
    public function sendData($data)
    {
        $headers = [
            'Accept' => 'application/json',
            'Accept-Language' => 'en-US',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => $this->getParameter('token'),
        ];

        $transactionReference = $this->getTransactionReference();

        $httpResponse = $this->httpClient->request(
            'POST',
            $this->getParameter('apiUrl') . '/api/payments/payment/' . $transactionReference . '/capture',
            $headers,
            json_encode($data)
        );

        $cancelResponseData = json_decode($httpResponse->getBody()->getContents(), true);

        $response = new CaptureAuthorizationResponse($this, $cancelResponseData);
        return $response;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->setParameter('token', $token);
    }

    /**
     * @param string $apiUrl
     */
    public function setApiUrl($apiUrl)
    {
        $this->setParameter('apiUrl', $apiUrl);
    }
}
