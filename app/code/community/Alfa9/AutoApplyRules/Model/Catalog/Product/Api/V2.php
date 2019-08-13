<?php
/**
 * Catalog product api V2
 *
 * @category   Alfa9
 * @package    Alfa9_AutoApplyRules
 * @author     Alfa9 Servicios Web, S.L. <ctubio@alfa9.com>
 */
class Alfa9_AutoApplyRules_Model_Catalog_Product_Api_V2 extends Mage_Catalog_Model_Product_Api_V2
{
    /**
     * Update product data
     *
     * @param int|string $productId
     * @param array $productData
     * @param string|int $store
     * @return boolean
     */
    public function update($productId, $productData, $store = null, $identifierType = null)
    {
      $result = parent::update($productId, $productData, $store, $identifierType);
      Mage::helper('a9auto_apply_rules')->autoApplyProductRules($productId);
      return $result;
    }
}
