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
 * Model login via Facebook
 *
 * @category   Oggetto
 * @package    Oggetto_Social
 * @subpackage Model
 * @author     Sergei Waribrus <svaribrus@oggettoweb.com>
 */
class Oggetto_Social_Model_Fb_Client extends Oggetto_Social_Model_Client_Abstract
{

    /**
     * The url to access the API
     *
     * @var string
     */
    protected $_urlMethodApi = 'https://graph.facebook.com/';

    /**
     * The url to obtain the access_token
     *
     * @var string
     */
    protected $_urlAccess = 'https://graph.facebook.com/oauth/access_token';

    /**
     * The url for authorize
     *
     * @var string
     */
    protected $_urlAuth = 'https://graph.facebook.com/oauth/authorize';

    /**
     * Access rights
     *
     * @var array
     */
    protected $_scope = array('email', 'offline_access');


    /**
     * Constructor method, filling in application settings
     *
     * @return void
     */
    public function __construct()
    {
        $this->_clientId = Mage::helper('oggetto_social/fb')->getClientId();
        $this->_clientSecret = Mage::helper('oggetto_social/fb')->getClientSecret();
        $this->_redirectUri = $redirectUrl = Mage::getUrl('social', array(
            '_query' => array('provider' => 'fb')
        ));
    }

    /**
     * Return user info
     *
     * @param string $code Code
     * @return mixed
     */
    public function getUserInfo($code)
    {
        $this->_generateAccessToken($code);
        $response = $this->_sendRequestToApi('me');

        return $response;
    }
}
