<?php
class Alfa9_Base_Model_Rss_Modules extends Mage_Core_Model_Abstract
{
  public function saveRss()
  { 
    try {
      $rss = 'a9base_modules_rss';
      if (!(Mage::app()->loadCache($rss)) ||
        (time() - Mage::app()->loadCache($rss.'_timestamp')) > Mage::getStoreConfig(Alfa9_Base_Helper_Data::XML_PATH_RSS_DELAY)
      ) {
        $channel = new Zend_Feed_Rss(Alfa9_Base_Helper_Data::RSS_MODULES_URL);
        $modules = array();
        foreach($channel as $item) {
          $modules[$item->module_id()] = array(
            'name' => $item->title(),
            'description' => $item->description(),
            'last_version' => $item->module_version(),
            'url' => $item->link()
          );
        }
        Mage::app()->saveCache(serialize($modules), $rss);
        Mage::app()->saveCache(time(), $rss.'_timestamp');
      }
    } catch (Exception $e) {}
  }
}