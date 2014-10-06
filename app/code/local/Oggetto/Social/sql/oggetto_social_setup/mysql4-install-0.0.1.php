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
 * Installer for module
 *
 * @category   Oggetto
 * @package    Oggetto_Social
 * @subpackage Installer
 * @author     Sergei Waribrus <svaribrus@oggettoweb.com>
 */

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->startSetup();

try {
    $attributes = array('vk_id' => 'Vk ID', 'fb_id' => 'Fb ID');
    foreach ($attributes as $code => $label) {
        $setup->addAttribute('customer', $code, array(
            'input'         => 'text',
            'type'          => 'text',
            'label'         => $label,
            'visible'       => 1,
            'required'      => 0,
            'user_defined'  => 1,
        ));

        $oAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', $code);
        $oAttribute->setData('used_in_forms', array('adminhtml_customer'));
        $oAttribute->save();

    }

} catch (Exception $e) {
    Mage::logException($e->getMessage());
}

$setup->endSetup();
