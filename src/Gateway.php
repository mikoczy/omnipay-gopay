<?php

namespace Omnipay\GoPay;

use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\GoPay\Message\AccessTokenRequest;
use Omnipay\GoPay\Message\AccessTokenResponse;
use Omnipay\GoPay\Message\CaptureAuthorizationRequest;
use Omnipay\GoPay\Message\CaptureAuthorizationResponse;
use Omnipay\GoPay\Message\PurchaseRequest;
use Omnipay\GoPay\Message\PurchaseResponse;
use Omnipay\GoPay\Message\RecurrenceRequest;
use Omnipay\GoPay\Message\CancelRecurrenceRequest;
use Omnipay\GoPay\Message\StatusRequest;
use Omnipay\GoPay\Message\Notification;
use Omnipay\GoPay\Message\VoidAuthorizationRequest;
use Omnipay\GoPay\Message\VoidAuthorizationResponse;

/**
 * GoPay payment gateway
 *
 * @package Omnipay\GoPay
 * @see https://doc.gopay.com/en/
 */
class Gateway extends AbstractGateway
{

    const URL_SANDBOX = 'https://gw.sandbox.gopay.com';
    const URL_PRODUCTION = 'https://gate.gopay.cz';

    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     */
    public function getName()
    {
        return 'GoPay';
    }

    /**
     * Get gateway short name
     *
     * This name can be used with GatewayFactory as an alias of the gateway class,
     * to create new instances of this gateway.
     */
    public function getShortName()
    {
        return 'gopay';
    }

    /**
     * Define gateway parameters, in the following format:
     *
     * array(
     *     'username' => '', // string variable
     *     'testMode' => false, // boolean variable
     *     'landingPage' => array('billing', 'login'), // enum variable, first item is default
     * );
     */
    public function getDefaultParameters()
    {
        return [
            'goId' => '',
            'clientId' => '',
            'clientSecret' => '',
            'testMode' => true,
        ];
    }

    /**
     * Initialize gateway with parameters
     */
    public function initialize(array $parameters = [])
    {
        parent::initialize($parameters);
        $this->setApiUrl();
        return $this;
    }

    /**
     * @param string $scope
     * @param string $grantType
     * @return AccessTokenResponse
     */
    public function getAccessToken($scope = 'payment-create', $grantType = 'client_credentials')
    {
        /** @var AccessTokenRequest $request */
        $request = parent::createRequest(AccessTokenRequest::class, array_merge($this->getParameters(), [
            'scope' => $scope,
            'grantType' => $grantType,
        ]));
        $response = $request->send();
        return $response;
    }

    /**
     * @param array $options
     * @return PurchaseResponse
     */
    public function authorize(array $options = array())
    {
        $options['purchaseData']['preauthorization'] = 'true';
        return $this->purchase($options);
    }

    /**
     * @param array $parameters
     * @return PurchaseResponse
     */
    public function completeAuthorize(array $parameters = array())
    {
        return $this->completePurchase($parameters);
    }

    /**
     * @param array $options
     * @return PurchaseResponse
     */
    public function purchase(array $options = array())
    {
        $this->setToken($this->getAccessToken()->getToken());
        $request = parent::createRequest(PurchaseRequest::class, $options);
        $response = $request->send();
        return $response;
    }

    /**
     * @param array $parameters
     * @return PurchaseResponse
     */
    public function completePurchase(array $parameters = array())
    {
        $this->setToken($this->getAccessToken()->getToken());
        $request = parent::createRequest(StatusRequest::class, $parameters);
        $response = $request->send();
        return $response;
    }

    /**
     * @param array $parameters
     * @return PurchaseResponse
     */
    public function status(array $parameters = array())
    {
        $this->setToken($this->getAccessToken()->getToken());
        $request = parent::createRequest(StatusRequest::class, $parameters);
        $response = $request->send();
        return $response;
    }

    /**
     * @param array $options
     * @return PurchaseResponse
     */
    public function recurrence(array $options = array())
    {
        $this->setToken($this->getAccessToken()->getToken());
        $request = parent::createRequest(RecurrenceRequest::class, $options);
        $response = $request->send();
        return $response;
    }

    /**
     * @param array $options
     * @return PurchaseResponse
     */
    public function cancelRecurrence(array $options = array())
    {
        $this->setToken($this->getAccessToken()->getToken());
        $request = parent::createRequest(CancelRecurrenceRequest::class, $options);
        $response = $request->send();
        return $response;
    }

    /**
     * @param array $options
     * @return CaptureAuthorizationResponse
     */
    public function capture(array $options = array())
    {
        $this->setToken($this->getAccessToken('payment-all')->getToken());
        $request = parent::createRequest(CaptureAuthorizationRequest::class, $options);
        $response = $request->send();
        return $response;
    }

    /**
     * @param array $options
     * @return VoidAuthorizationResponse
     */
    public function voidAuthorization(array $options = array())
    {
        $this->setToken($this->getAccessToken('payment-all')->getToken());
        $request = parent::createRequest(VoidAuthorizationRequest::class, $options);
        $response = $request->send();
        return $response;
    }

    public function acceptNotification()
    {
        $this->setToken($this->getAccessToken()->getToken());
        $parameters = ['transactionReference' => $this->httpRequest->query->get('id')];
        $request = parent::createRequest(StatusRequest::class, $parameters);
        /** @var PurchaseResponse $response */
        $response = $request->send();
        $parameters = [
            'code' => $response->getCode(),
            'transactionReference' => $response->getTransactionReference(),
            'transactionId' => $response->getTransactionId(),
            'data' => $response->getData()
        ];

        return new Notification($this->httpRequest, $this->httpClient, $parameters);
    }

    public function setGoId($goId)
    {
        $this->setParameter('goId', $goId);
    }

    public function setClientId($clientId)
    {
        $this->setParameter('clientId', $clientId);
    }

    public function setClientSecret($clientSecret)
    {
        $this->setParameter('clientSecret', $clientSecret);
    }

    public function setApiUrl()
    {
        if ($this->getTestMode()) {
            $apiUrl = self::URL_SANDBOX;
        } else {
            $apiUrl = self::URL_PRODUCTION;
        }

        $this->setParameter('apiUrl', $apiUrl);
    }

    private function setToken($token)
    {
        $this->setParameter('token', $token);
    }

}