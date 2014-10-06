<?php
/**
 * Oggetto Web extension for Magento
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
 * Controller login social network
 *
 * @category   Oggetto
 * @package    Oggetto_Social
 * @subpackage Controller
 * @author     Sergei Waribrus <svaribrus@oggettoweb.com>
 */

class Oggetto_Social_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Name social network
     *
     * @var string
     */
    protected $_provider;

    /**
     * Predispatch: should set layout area
     *
     * @return $this|Mage_Core_Controller_Front_Action
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        $this->_provider = $this->getRequest()->getParam('provider');

        if ($this->_provider != 'vk' && $this->_provider != 'fb') {
            $this->setFlag('', 'no-dispatch', true);
            $this->_redirectReferer();
            Mage::log($this->__('Provider not found.'));
            return;
        }

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->setFlag('', 'no-dispatch', true);
            $this->_redirectReferer();
            Mage::log($this->__('You logged in.'));
            return;
        }

        if (!Mage::helper('oggetto_social/' . $this->_provider)->isEnabled()) {
            if (!Mage::getSingleton('customer/session')->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
            }
            $this->_redirectReferer();
            Mage::log($this->__('Login Social disabled.'));
            return;
        }


    }

    /**
     * The method connect social network
     *
     * @return void
     */
    public function indexAction()
    {
        try {
            $errorCode = $this->getRequest()->getParam('error');
            $code = $this->getRequest()->getParam('code');

            if ($errorCode) {
                throw new Exception(
                    Mage::helper('oggetto_social/' . $this->_provider)->__(
                        'Sorry, "%s" error occurred. Please try again.',
                        $errorCode
                    )
                );
            }

            if ($code) {
                $client = Mage::getSingleton('oggetto_social/' . $this->_provider . '_client');
                $userInfo = $client->getUserInfo($code);
                $status = $this->_login($userInfo);
            } else {
                throw new Exception($this->__('Sorry, code is not passed error occurred. Please try again.'));
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }

        $this->loadLayout();

        $this->getLayout()->getBlock('root')->setData(array(
            'status'   => $status,
            'provider' => $this->_provider
        ));

        $this->renderLayout();
    }

    /**
     * The authorization logic
     *
     * @param array $userInfo information from the social network
     * @return string
     */
    protected function _login($userInfo)
    {
        /** @var $helper Oggetto_Social_Helper_Social_Abstract */
        $helper = Mage::helper('oggetto_social/' . $this->_provider);

        $customer = $helper->getCustomerBySocialId($userInfo['id']);
        if ($customer != null) {
            $helper->loginCustomer($customer);
            return 'login social';
        }

        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer = $customer->loadByEmail($userInfo['email']);
        if ($customer->getId() != null) {
            $helper->connectBySocialId(
                $customer,
                $userInfo['id']
            );
            $helper->loginCustomer($customer);

            return 'login social';
        }

        $customer = $helper->createCustomer(
            $userInfo['email'],
            $userInfo['first_name'],
            $userInfo['last_name'],
            $userInfo['id']
        );
        $helper->sendPasswordEmail($customer);
        $helper->loginCustomer($customer);
        return 'registration social';
    }
}
