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
 * Model login via Vkontakte
 *
 * @category   Oggetto
 * @package    Oggetto_Social
 * @subpackage Model
 * @author     Sergei Waribrus <svaribrus@oggettoweb.com>
 */

class Oggetto_Social_Model_Vk_Client extends Oggetto_Social_Model_Client_Abstract
{

    /**
     * The url to access the API
     *
     * @var string
     */
    protected $_urlMethodApi = 'https://api.vk.com/method/';

    /**
     * The url to obtain the access_token
     *
     * @var string
     */
    protected $_urlAccess = 'https://oauth.vk.com/access_token';

    /**
     * The url for authorize
     *
     * @var string
     */
    protected $_urlAuth = 'https://oauth.vk.com/authorize';

    /**
     * Access rights
     *
     * @var array
     */
    protected $_scope = array('offline', 'email');

    /**
     * Construct method, filling in application settings
     *
     * @return void
     */
    public function __construct()
    {
        $this->_clientId = Mage::helper('oggetto_social/vk')->getClientId();
        $this->_clientSecret = Mage::helper('oggetto_social/vk')->getClientSecret();
        $this->_redirectUri = Mage::getUrl('social', array(
            '_query' => array('provider' => 'vk')
        ));
    }

    /**
     * Return user info
     *
     * @param string $code Code
     * @return array
     */
    public function getUserInfo($code)
    {
        $this->_generateAccessToken($code);
        $response = $this->_sendRequestToApi('getProfiles');

        $info = $response['response'][0];
        $info['id'] = $info['uid'];
        unset($info['uid']);

        return array_merge(array(
            'email' => $this->_email
        ), $info);
    }

    /**
     * Return token
     *
     * @param string $code Code
     * @return void
     */
    protected function _generateAccessToken($code)
    {
        parent::_generateAccessToken($code);
        $this->_email = $this->_response['email'];
    }

    /**
     * A method to handle the response from the server
     *
     * @param string $response the response from the server
     * @return mixed
     */
    protected function _decodeResponse($response)
    {
        return json_decode($response->getBody(), $assoc = true);
    }

}
