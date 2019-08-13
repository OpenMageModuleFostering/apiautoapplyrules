<?php
/**
 * Alfa9 AutoApplyRules Helper
 *
 * @category   Alfa9
 * @package    Alfa9_AutoApplyRules
 * @author     Alfa9 Servicios Web, S.L. <ctubio@alfa9.com>
 */
class Alfa9_AutoApplyRules_Helper_Data extends Mage_Core_Helper_Abstract
{   
  const XML_PATH_A9AAR_ENABLED = 'a9auto_apply_rules/options/enabled';
  
  public function autoApplyProductRules($productId) {
    if (!Mage::getStoreConfig(Alfa9_AutoApplyRules_Helper_Data::XML_PATH_A9AAR_ENABLED)) return;
	  try {
      $product = Mage::getModel('catalog/product')->load($productId);
      if (!$product) return;
	  	$productWebsiteIds = $product->getWebsiteIds();
	  	$rules = Mage::getModel('catalogrule/rule')->getCollection()
        ->addFieldToFilter('is_active', 1);
	  	foreach ($rules as $rule) {
	  		if ($rule->getConditions()->validate($product)) {	
	  			if (!is_array($rule->getWebsiteIds())) {
	  				$ruleWebsiteIds = (array)explode(',',$rule->getWebsiteIds());
	  			} else {
	  				$ruleWebsiteIds = $rule->getWebsiteIds();
	  			}
		  		$websiteIds = array_intersect($productWebsiteIds, $ruleWebsiteIds);
		  		$rule->applyToProduct($product, $websiteIds);
	  		}
	  	}
	  	Mage::log('Applied catalogrules to productId: '.$productId.'.');
	  } catch(Exception $e) {
	  	Mage::logException($e);
	  	Mage::log('Failed to apply catalogrules to productId: '.$productId.'.');
	  }
  }
}