<?php
class Alfa9_MageForce_Model_Observer
{   
    private function getClassSuffix() {
      $class_name = get_class($this);
      return strtolower(substr($class_name, strrpos($class_name,'_')+1));
    }
    
    private function config($option) {
      $return = Mage::getStoreConfig(
        constant('Alfa9_MageForce_Helper_Data::XML_PATH_MAP_'.$this->getClassSuffix().'_'.$option)
      );
      if ($option=='ua_attr') {
        $return = @unserialize($return);
        if (!is_array($return)) $return = array();
      }
      return $return;
    }
    
    private function getIdField() {
      $idField = NULL;
      foreach($this->config('ua_attr') as $ua_attr)
        if ($ua_attr['magento']=='entity_id') {
          $idField = $ua_attr['salesforce'];
          break;
        }
      return $idField;
    }
    
    private function getData($object) {
      $data = array();
      foreach($this->config('ua_attr') as $ua_attr)
        $data[$ua_attr['salesforce']] = Mage::helper('a9mageforce')->getDataMapFixed(
          $this->getClassSuffix(),
          $ua_attr['magento'],
          $ua_attr['salesforce'],
          $object->getData($ua_attr['magento']),
          $object
        );
      try {
        foreach($data as $k => $v) if (!$k or is_null($v)) unset($data[$k]);
      } catch (Exception $e) {
        Mage::logException($e);
      }
      return $data;
    }
    
    public function upsert(Varien_Event_Observer $observer) {
      if (!$this->config('enabled') or Mage::registry('SKIP_NEXT_'.__FUNCTION__))
        return;
      try {
        $entity = $this->getClassSuffix();
        $object = $observer->getData($entity);
        $return = Mage::helper('a9mageforce')->callSF(
          'upsert',
          $entity,
          $this->getIdField(),
          array($this->config('salesforce_entity') => $this->getData($object))
        );
        if($return[0]->success and $return[0]->id and $return[0]->id!=$object->getSalesforceId()) {
          Mage::register('SKIP_NEXT_'.__FUNCTION__, TRUE);
          $object->setSalesforceId($return[0]->id)->save();
          Mage::unregister('SKIP_NEXT_'.__FUNCTION__);
        }
      } catch (Exception $e) {
        Mage::logException($e);
      }
    }
    
    public function delete(Varien_Event_Observer $observer) {
      if (!$this->config('enabled')) return;
      try {
        $entity = $this->getClassSuffix();
        $object = $observer->getData($entity);
        Mage::helper('a9mageforce')->callSF(
          'delete',
          $entity,
          array($object->getSalesforceId())
        );
      } catch (Exception $e) {
        Mage::logException($e);
      }
    }
}