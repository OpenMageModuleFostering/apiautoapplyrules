<?php
class Alfa9_VentasEnGrupo_Model_Dailydeal_Task
{
  public function run()
  {
    ini_set('memory_limit', '512M');
    foreach(array_slice(Mage::app()->getStores(), 1) as $store) {
      date_default_timezone_set(Mage::app()->getStore($store)->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE));
      foreach(Mage::helper('a9veg')->getStoreSaleWeeks(time(), $store) as $saleWeek) {
        foreach(Mage::getModel('catalog/product')
          ->getCollection()
          ->setStore($store)
          ->addAttributeToSelect('name')
          ->addAttributeToSelect('price')
          ->addAttributeToFilter('status', 1)
          ->addAttributeToFilter('visibility', 4)
          ->load()
        as $product) {
          try {
            Mage::getModel('dailydeal/dailydeal')
              ->setTitle('Deal '.$store->getName().': '.$product->getName())
              ->setProductId($product->getId())
              ->setProductName($product->getName())
              ->setDealPrice(number_format($product->getPrice(),2,'.',','))
              ->setQuantity('1000')
              ->setStartTime($saleWeek->getBegin())
              ->setCloseTime($saleWeek->getEnd())
              ->setStoreId($store->getStoreId())
              ->save();
          } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('core/session')->addError(
              Mage::helper('catalog')->__($e->getMessage())
            );
            return;
          }
        }
      }
    }
  }
}
