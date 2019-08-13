<?php
require_once(Mage::getBaseDir() . '/lib/salesforceAPI/soapclient/SforceEnterpriseClient.php');

class Alfa9_MageForce_Helper_Data extends Mage_Core_Helper_Abstract
{
    const VAR_LOG_FILE = 'salesforce.log';

    const XML_PATH_LOGIN_USERNAME		= 'a9mageforce/login/username';
    const XML_PATH_LOGIN_PASSWORD		= 'a9mageforce/login/password';
    const XML_PATH_LOGIN_TOKEN		  = 'a9mageforce/login/token';

    const XML_PATH_WSDL_enterprise  = 'a9mageforce/wsdl/enterprise';
    const XML_PATH_WSDL_metadata		= 'a9mageforce/wsdl/metadata';

    const XML_PATH_DEBUG_FILE_ENABLED	= 'a9mageforce/debug/file_enabled';
    const XML_PATH_DEBUG_MAIL_ENABLED	= 'a9mageforce/debug_mail/enabled';
    const XML_PATH_DEBUG_MAIL_FROM    = 'a9mageforce/debug/mail_from';
    const XML_PATH_DEBUG_MAIL_TO     	= 'a9mageforce/debug/mail_to';

    const XML_PATH_MAP_customer_enabled	          = 'a9mageforce/map_customer/enabled';
    const XML_PATH_MAP_customer_magento_entity	  = 'a9mageforce/map_customer/magento_entity';
    const XML_PATH_MAP_customer_salesforce_entity	= 'a9mageforce/map_customer/salesforce_entity';
    const XML_PATH_MAP_customer_ua_attr	          = 'a9mageforce/map_customer/ua_attr';

    const XML_PATH_MAP_product_enabled	          = 'a9mageforce/map_product/enabled';
    const XML_PATH_MAP_product_magento_entity  	  = 'a9mageforce/map_product/magento_entity';
    const XML_PATH_MAP_product_salesforce_entity	= 'a9mageforce/map_product/salesforce_entity';
    const XML_PATH_MAP_product_ua_attr	          = 'a9mageforce/map_product/ua_attr';

    const XML_PATH_MAP_quote_enabled	            = 'a9mageforce/map_quote/enabled';
    const XML_PATH_MAP_quote_magento_entity	      = 'a9mageforce/map_quote/magento_entity';
    const XML_PATH_MAP_quote_salesforce_entity 	  = 'a9mageforce/map_quote/salesforce_entity';
    const XML_PATH_MAP_quote_ua_attr	            = 'a9mageforce/map_quote/ua_attr';

    const XML_PATH_MAP_order_enabled	            = 'a9mageforce/map_order/enabled';
    const XML_PATH_MAP_order_magento_entity	      = 'a9mageforce/map_order/magento_entity';
    const XML_PATH_MAP_order_salesforce_entity  	= 'a9mageforce/map_order/salesforce_entity';
    const XML_PATH_MAP_order_ua_attr	            = 'a9mageforce/map_order/ua_attr';

    public function log($log) {
      if (Mage::getStoreConfig(self::XML_PATH_DEBUG_FILE_ENABLED))
        Mage::log($log, NULL, self::VAR_LOG_FILE);
    }

    public function getWSDL($wsdl = 'enterprise') {
      return Mage::getBaseDir() . '/lib/salesforceAPI/soapclient/wsdl/'
        . Mage::getStoreConfig(constant('self::XML_PATH_WSDL_'.$wsdl));
    }

    public function callSF($method, $entity, $id = NULL, $data = NULL) {
      $return = array();
      $this->log('OPENING REMOTE CONNECTION: '.strtoupper($entity.' '.$method));
      try {
        $sfdc = new SforceEnterpriseClient();
        $client = $sfdc->createConnection($this->getWSDL());
        $sfdc->login(Mage::getStoreConfig(self::XML_PATH_LOGIN_USERNAME), Mage::getStoreConfig(self::XML_PATH_LOGIN_PASSWORD). Mage::getStoreConfig(self::XML_PATH_LOGIN_TOKEN));
        $this->log(strtoupper($entity.' '.$method).': LOGGED. STARTING DATA TRANSFER.');
        switch($method) {
          case 'upsert':
            foreach($data as $entity => $object)
              $return[] = call_user_func_array(
                array($sfdc, $method),
                array($id, array((object)$object), $entity)
              );
            break;
          default:
            $return[] = call_user_func_array(array($sfdc, $method), $id);
            break;
        }
        $this->log(strtoupper($entity.' '.$method).': SET REQUEST:'.PHP_EOL.$client->__getLastRequest());
        $this->log(strtoupper($entity.' '.$method).': GET RESPONSE:'.PHP_EOL.$client->__getLastResponse());
        $sfdc->logout();
        $this->log(strtoupper($entity.' '.$method).': LOGOUT. DATA TRANSMISSION FINISHED.');
      } catch (Exception $e) {
          Mage::logException($e);
          $this->log(strtoupper($entity.' '.$method).': ERROR:'.PHP_EOL.$e->getMessage());
          try {
            $request = strtoupper($entity.' '.$method).': SET REQUEST:'.PHP_EOL.$client->__getLastRequest();
            $response = strtoupper($entity.' '.$method).': GET RESPONSE:'.PHP_EOL.$client->__getLastResponse();
            $this->log($request);
            $this->log($response);
            if (Mage::getStoreConfig(self::XML_PATH_DEBUG_MAIL_ENABLED) and Mage::getStoreConfig(self::XML_PATH_DEBUG_MAIL_TO) and Mage::getStoreConfig(self::XML_PATH_DEBUG_MAIL_FROM))
              Mage::getModel('core/email')
                ->setToName(Mage::getStoreConfig(self::XML_PATH_DEBUG_MAIL_TO))
                ->setToEmail(Mage::getStoreConfig(self::XML_PATH_DEBUG_MAIL_TO))
                ->setSubject('SalesForce Error: '.strtoupper($entity.' '.$method))
                ->setBody($request.$response)
                ->setFromEmail(Mage::getStoreConfig(self::XML_PATH_DEBUG_MAIL_FROM))
                ->setFromName(Mage::getStoreConfig(self::XML_PATH_DEBUG_MAIL_FROM))
                ->setType('text')
                ->send();
          } catch (Exception $e) {}
      }
      $this->log('REMOTE CONNECTION CLOSED: '.strtoupper($entity.' '.$method));
      return (count($return)==1) ? current($return) : $return;
    }

    public function getDataKeysFromMagento($entity) {
      $attributes = array();
      $model = Mage::getStoreConfig(constant('self::XML_PATH_MAP_'.$entity.'_magento_entity'));
      if (in_array($entity, array('product', 'customer')))
          $attributes = array_keys(Mage::getModel($model)->getAttributes());
      else if (in_array($entity, array('quote', 'order')))
        foreach(Mage::getSingleton('core/resource')
          ->getConnection('core_write')
          ->query("SHOW COLUMNS FROM ".Mage::getResourceModel($model)->getTable($model))
          ->fetchAll()
        as $attr)
          array_push($attributes, $attr['Field']);
      return $attributes;
    }

    public function getDataKeysFromSalesforce($entity) {
      $attributes = array();
      $xml = simplexml_load_file($this->getWSDL());
      $xml->registerXPathNamespace('api', 'http://schemas.xmlsoap.org/wsdl/');
      $a = $xml->xpath('//api:types');
      $xml->registerXPathNamespace('api', 'http://www.w3.org/2001/XMLSchema');
      $xpath = $xml->xpath('//api:complexType[@name="'.Mage::getStoreConfig(constant('self::XML_PATH_MAP_'.$entity.'_salesforce_entity')).'"]');
      foreach($xpath[0]->complexContent->extension->sequence->element as $element)
        foreach($element->attributes() as $attr => $value)
          if ($attr=='name') array_push($attributes, $value->__toString());
      return $attributes;
    }

    public function getDataMapFixed($entity, $magento, $salesforce, $attr, $object) {
      switch($entity) {
        case 'product':
          if ($magento=='url_path' and $salesforce=='URL__c')
            $attr = Mage::app()->getStore()->getUrl().$attr;
          break;
        case 'quote':
        case 'order':
          if ($salesforce=='Magento_ID__c')
            $attr = substr($entity, 0, 1).$attr;
          else if ($magento=='customer_is_guest' and $salesforce=='Type')
            $attr = $attr ? 'New Customer' : 'Existing Customer - Upgrade';
          else if ($magento=='entity_id' and $salesforce=='Name')
            $attr = 'Quote '.$attr;
          if ($magento=='grand_total' and $salesforce=='Amount')
            $attr = (float)$attr;
          else if ($magento=='customer_id' and $salesforce=='AccountId')
            $attr = Mage::getModel('customer/customer')->load($attr)->getSalesforceId();
          else if ($magento=='is_active' and $salesforce=='StageName')
            if ($attr)
              $attr = 'Proposal/Price Quote';
            else if($object->getReservedOrderId())
              $attr = 'Closed Won';
            else
              $attr = 'Closed Lost';
        case 'order':
          if ($magento=='increment_id' and $salesforce=='Name')
            $attr = 'Order '.$attr;
          if ($magento=='total_paid' and $salesforce=='Amount')
            $attr = (float)$attr;
          else if ($magento=='state' and $salesforce=='StageName')
            if ($attr=='complete')
              $attr = 'Closed Won';
            else if($attr=='canceled')
              $attr = 'Closed Lost';
            else
              $attr = 'Proposal/Price Quote';
          break;
      }
      return (is_scalar($attr)) ? $attr : (is_null($attr) ? '' : @serialize($attr));
    }
}