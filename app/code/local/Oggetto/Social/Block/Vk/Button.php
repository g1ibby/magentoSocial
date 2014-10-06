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
 * Block is responsible for displaying the login button Vkontakte
 *
 * @category   Oggetto
 * @package    Oggetto_Social
 * @subpackage Block
 * @author     Sergei Waribrus <svaribrus@oggettoweb.com>
 */
class Oggetto_Social_Block_Vk_Button extends Mage_Core_Block_Template
{
    /**
     * Model for communication with social networking
     *
     * @var Oggetto_Social_Model_Fb_Client | Oggetto_Social_Model_Vk_Client
     */
    protected $_client;

    /**
     * Constructor method
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_client = Mage::getSingleton('oggetto_social/vk_client');

        if (!Mage::helper('oggetto_social/vk')->isEnabled() || Mage::getSingleton('customer/session')->isLoggedIn()) {
            return;
        }

        $this->setTemplate('oggetto/social/vk/button.phtml');
    }

    /**
     * Returns the URL for the code
     *
     * @return string
     */
    protected function _getButtonUrl()
    {
        $url = $this->_client->getUrlAuth() . '?';
        $scope = implode(',', $this->_client->getScope());
        $clientId = $this->_client->getClientId();
        $redirectUrl = $this->_client->getRedirectUrl();

        $url .= http_build_query(array(
            'client_id'     => $clientId,
            'scope'         => $scope,
            'redirect_uri'  => $redirectUrl,
            'response_type' => 'code'
        ));

        return $url;
    }

    /**
     * Return text for button
     *
     * @return string
     */
    protected function _getButtonText()
    {
        return $this->__('vk login');
    }

}
