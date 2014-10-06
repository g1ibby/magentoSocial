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
 * Parent class for Helper social network
 *
 * @category   Oggetto
 * @package    Oggetto_Social
 * @subpackage Helper
 * @author     Sergei Waribrus <svaribrus@oggettoweb.com>
 */
abstract class Oggetto_Social_Helper_Social_Abstract extends Mage_Core_Helper_Abstract
{
    /**
     * The name for the social network
     *
     * @var string
     */
    protected $_socialName;

    /**
     * Send email with your password
     *
     * @param Mage_Customer_Model_Customer $customer Customer
     *
     * @return void
     */
    public function sendPasswordEmail($customer)
    {
        $password = $customer->generatePassword(10);
        $customer->setPassword($password)->save();

        $template = Mage::getStoreConfig('social_login/oggetto_social_login/welcome_email');

        $senderName = Mage::getStoreConfig('trans_email/ident_support/name');
        $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');

        Mage::getModel('core/email_template')
            ->sendTransactional($template,
                $sender = array(
                      'name'  => $senderName,
                      'email' => $senderEmail
                ),
                $email  = $customer->getEmail(),
                $name   = $customer->getFirstName(),
                $vars   = array('customer' => $customer)
            );
    }

    /**
     * Login customer
     *
     * @param Mage_Customer_Model_Customer $customer Customer
     * @return void
     */
    public function loginCustomer(Mage_Customer_Model_Customer $customer)
    {
        $customer->setConfirmation(null);
        $customer->save();

        Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
    }

    /**
     * Creates a new user and connect social network
     *
     * @param string $email     Email
     * @param string $firstName first name
     * @param string $lastName  last name
     * @param string $id        id Facebook
     * @return Mage_Customer_Model_Customer
     */
    public function createCustomer($email, $firstName, $lastName, $id)
    {
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getWebsite()->getId())
            ->setEmail($email)
            ->setFirstname($firstName)
            ->setLastname($lastName);
        $this->linkCustomerToSocial($customer, $id);

        $customer->setConfirmation(null);
        $customer->save();

        return $customer;
    }

    /**
     * Connect your social network to the existing user
     *
     * @param Mage_Customer_Model_Customer $customer Customer
     * @param string                       $id       social ID
     * @return void
     */
    public function connectBySocialId(Mage_Customer_Model_Customer $customer, $id)
    {
        $this->linkCustomerToSocial($customer, $id);
        $customer->save();
    }

    /**
     * Connects customer and social network
     *
     * @param string $customer Customer
     * @param string $socialId Social Id
     * @return void
     */
    public function linkCustomerToSocial($customer, $socialId)
    {
        $setSocialId = 'set' . $this->_socialName . 'Id';
        $customer->$setSocialId($socialId);
    }


    /**
     * Get user ID social network
     *
     * @param string $id social ID
     * @return null
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomerBySocialId($id)
    {
        $customer = Mage::getModel('customer/customer');

        $attribute = mb_strtolower($this->_socialName) . '_id';

        $collection = $customer->getCollection()
            ->addAttributeToFilter($attribute, $id)
            ->setPageSize(1);


        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $collection->addAttributeToFilter(
                'website_id',
                Mage::app()->getWebsite()->getId()
            );
        }

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $collection->addFieldToFilter(
                'entity_id',
                array('neq' => Mage::getSingleton('customer/session')->getCustomerId())
            );
        }
        if ($collection->count() != 0) {
            return $collection->getFirstItem();
        } else {
            return null;
        }
    }

    /**
     * Logging is enabled by social network
     *
     * @return bool
     */
    public function isEnabled()
    {
        if ($this->isEnabledSocialLogin() && (bool) Mage::getStoreConfig('social_login/oggetto_social_'
                . mb_strtolower($this->_socialName) . '/enabled')) {
            return true;
        }
        return false;
    }

    /**
     * Get status vk module
     *
     * @return bool
     */
    public function isEnabledSocialLogin()
    {
        return (bool) Mage::getStoreConfig('social_login/oggetto_social_login/enabled');
    }

    /**
     * Get client id
     *
     * @return string
     */
    public function getClientId()
    {
        return Mage::getStoreConfig('social_login/oggetto_social_'
            . mb_strtolower($this->_socialName) .'/client_id');
    }

    /**
     * Get client secret
     *
     * @return string
     */
    public function getClientSecret()
    {
        return Mage::getStoreConfig('social_login/oggetto_social_'
            . mb_strtolower($this->_socialName) . '/client_secret');
    }

}