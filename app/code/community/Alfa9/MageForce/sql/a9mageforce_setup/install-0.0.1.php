<?php

$installer = $this;
 
$installer->startSetup();
$installer->addAttribute('catalog_product', 'salesforce_id', array(
    'group'             => 'General',
    'label'             => 'SalesForce ID',
    'note'              => 'Leave empty if you are creating a new product.',
    'type'              => 'varchar',
    'input'             => 'text',
    'frontend_class'    => '',
    'source'            => '',
    'backend'           => '',
    'frontend'          => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'required'          => false,
    'visible_on_front'  => false,
    'apply_to'          => 'simple',
    'is_configurable'   => false,
    'used_in_product_listing'   => false,
    'sort_order'        => 4,
));

$setup = Mage::getModel('customer/entity_setup', 'core_setup');
$setup->addAttribute('customer', 'salesforce_id', array(
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'SalesForce ID',
    'note' => 'Leave empty if you are creating a new customer.',
    'global' => 1,
    'visible' => 1,
    'required' => 0,
    'user_defined' => 0,
    'default' => '',
    'visible_on_front' => 0,
));
if (version_compare(Mage::getVersion(), '1.6.0', '<=')) {
      $customer = Mage::getModel('customer/customer');
      $attrSetId = $customer->getResource()->getEntityType()->getDefaultAttributeSetId();
      $setup->addAttributeToSet('customer', $attrSetId, 'General', 'salesforce_id');
}
if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
    Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'salesforce_id')
    ->setData('used_in_forms', array(
      'adminhtml_customer'
    ))
    ->save();
}

$setup = new Mage_Sales_Model_Resource_Setup('core_setup');
$entities = array(
    'quote',
    #'quote_address',
    #'quote_item',
    #'quote_address_item',
    'order',
    #'order_item'
);
$options = array(
    'type'     => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'visible'  => true,
    'required' => false
);
foreach ($entities as $entity) {
    $setup->addAttribute($entity, 'salesforce_id', $options);
}
$installer->endSetup();