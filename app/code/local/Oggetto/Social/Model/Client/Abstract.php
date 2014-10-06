<?php
/**
 * Oggetto Web Social login extension for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Oggetto Social module to newer versions in the future.
 * If you wish to customize the Oggetto Social module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Oggetto
 * @package    Oggetto_Social
 * @copyright  Copyright (C) 2014 Oggetto Web ltd (http://oggettoweb.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
/**
 * Parent model to communicate with social networking
 *
 * @category   Oggetto
 * @package    Oggetto_Social
 * @subpackage Model
 * @author     Sergei Waribrus <svaribrus@oggettoweb.com>
 */
abstract class Oggetto_Social_Model_Client_Abstract
{

    /**
     * Key into the application to access API
     *
     * @var string
     */
    protected $_token;

    /**
     * Email customer
     *
     * @var string
     */
    protected $_email;

    /**
     * Id app social network
     *
     * @var string
     */
    protected $_clientId;

    /**
     * Secret key access app social network
     *
     * @var string
     */
    protected $_clientSecret;

    /**
     * The url that is returned by the access code
     *
     * @var string
     */
    protected $_redirectUri;

    /**
     * The url to access the API
     *
     * @var string
     */
    protected $_urlMethodApi;

    /**
     * The url to obtain the access_token
     *
     * @var string
     */
    protected $_urlAccess;

    /**
     * The url for authorize
     *
     * @var string
     */
    protected $_urlAuth;

    /**
     * Access rights
     *
     * @var array
     */
    protected $_scope;

    /**
     * The response from the server when receiving access_token
     *
     * @var array
     */
    protected $_response;

    /**
     * Method for social api
     *
     * @param string $methodName Method Name
     * @return mixed
     */
    protected function _sendRequestToApi($methodName)
    {
        $url = $this->_urlMethodApi . $methodName;

        $params = array(
            'access_token' => $this->_token,
        );

        $response = $this->_httpRequest($url, $params);

        return $response;
    }

    /**
     * Return redirect url
     *
     * @return String
     */
    public function getRedirectUrl()
    {
        return $this->_redirectUri;
    }

    /**
     * Return client id
     *
     * @return String
     */
    public function getClientId()
    {
        return $this->_clientId;
    }

    /**
     * Return url authorization
     *
     * @return String
     */
    public function getUrlAuth()
    {
        return $this->_urlAuth;
    }

    /**
     * Return scope
     *
     * @return array
     */
    public function getScope()
    {
        return $this->_scope;
    }

    /**
     * Sends a request to the server, the social network
     *
     * @param string $url    URL
     * @param array  $params Params
     * @return mixed
     * @throws Exception
     * @return array
     */
    protected function _httpRequest($url, $params = array())
    {
        $client = new Zend_Http_Client($url, array('timeout' => 60));
        $client->setParameterGet($params);

        $response = $client->request('GET');

        return $this->_decodeResponse($response);
    }

    /**
     * Gets the token
     *
     * @param string $code Code
     * @throws Exception Access token not received
     * @return void
     */
    protected function _generateAccessToken($code)
    {
        $this->_response = $this->_httpRequest(
            $this->_urlAccess,
            array(
                'code' => $code,
                'redirect_uri' => $this->_redirectUri,
                'client_id' => $this->_clientId,
                'client_secret' => $this->_clientSecret
            )
        );


        $errorCode = $this->_response['error'];
        if ($errorCode) {
            throw new Exception(
                sprintf(
                    'Sorry, "%s" error occured. Please try again.',
                    $this->_response['error_description']
                )
            );
        }

        $this->_token = $this->_response['access_token'];
    }

    /**
     * A method to handle the response from the server
     *
     * @param string $response The response from the server
     * @return mixed
     */
    protected function _decodeResponse($response)
    {
        $decodedResponse = json_decode($response->getBody(), $assoc = true);

        if (empty($decodedResponse)) {
            $parsedResponse = array();
            parse_str($response->getBody(), $parsedResponse);

            $decodedResponse = json_decode(json_encode($parsedResponse), true);
        }

        return $decodedResponse;
    }
}