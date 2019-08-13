<?php
class Alfa9_Base_Block_System_Config_Form_Fieldset extends Mage_Adminhtml_Block_Template
{
  private $currentSection = NULL;
  
  private $modules = NULL;
 
  protected $modules_rss = array();
  
  protected function getCurrentSection()
  {
    if (is_null($this->currentSection)) {
      $this->currentSection = $this->getAction()->getRequest()->getParam('section', FALSE);
    }
    return $this->currentSection;
  }

  protected function _toHtml()
  {
    return (in_array($this->getCurrentSection(), array('a9extensions', 'a9store')))
      ? parent::_toHtml()
      : NULL;
  }

  protected function getModules()
  {
    if (is_null($this->modules)) {
      $modules = Mage::getConfig()->getNode('modules')->children();
      $this->modules = array();
      foreach ($modules as $moduleId => $module) {
        if (strstr($moduleId, 'Alfa9_')===FALSE) continue;
        $rssData = $this->getModuleRss($moduleId);
        $this->modules[] = new Varien_Object(array(
          'module_id'    => $moduleId,
          'name'         => $rssData->getName()?$rssData->getName():($module->name?$module->name:$moduleId),
          'active'       => !Mage::getStoreConfig('advanced/modules_disable_output/'.$moduleId),
          'version'      => $module->version,
          'last_version' => $rssData->getLastVersion(),
          'url'          => $rssData->getUrl(),
          'description'  => $rssData->getDescription(),
        ));
      }
      usort($this->modules, create_function('$a,$b','$a=$a["name"];$b=$b["name"];return strcmp($a,$b);'));
    }
    return $this->modules;
  }

  /**
   * @param $moduleId
   * @return bool|Varien_Object
   */
  private function getModuleRss($moduleId)
  {
      if (!count($this->modules_rss)) {
          if ($modules_rss = Mage::app()->loadCache('a9base_modules_rss')) {
              $this->modules_rss = @unserialize($modules_rss);
          }
      }
      $module_rss = array();
      if (array_key_exists($moduleId, $this->modules_rss)) {
          $module_rss = @$this->modules_rss[$moduleId];
      }
      return new Varien_Object($module_rss);
  }

  /**
   * Fetch store data and return as Varien Object
   * @return Varien_Object
   */
  protected function getExtensions()
  {
    if (!(Mage::app()->loadCache('alfa9_modules_html')) ||
      (time() - Mage::app()->loadCache('alfa9_modules_html_timestamp')) > Mage::getStoreConfig(Alfa9_Base_Helper_Data::XML_PATH_RSS_DELAY)
    ) {
      $client = new Zend_Http_Client(Alfa9_Base_Helper_Data::HTML_MODULES_URL);
      $response = $client->request();
      if ($response->getStatus()==200) {
        $extensions = new Zend_Dom_Query($response->getBody());
        Mage::app()->saveCache(time(), 'alfa9_modules_html_timestamp');
      } else {
        $extensions = NULL;
        Mage::getSingleton('adminhtml/session')->addError($this->__('Our online shop is under maintenance right now. We are sorry for the inconveniences, but we will return back to bussines shortly.'));
      }
      Mage::app()->saveCache(serialize($extensions), 'alfa9_modules_html');
    }
    return @unserialize(Mage::app()->loadCache('alfa9_modules_html'));
  }
}
