<?php
/**
 * Order Observer Model
 *
 * @category    Model
 * @package     Alfa9
 * @author      ctubio <ctubio@alfa9.com>
 */

class Alfa9_VentasEnGrupo_Block_Grupo_Selector extends Mage_Core_Block_Template {
  
  public function __construct()
  {
    parent::__construct();
    
    if (Mage::helper('a9veg')->isMainStore())
      $this->setTemplate('alfa9/grupo_consumo/selector.phtml');
      
  }
  
  public function getStores() {
    return Mage::app()->getStores();
  }
}
?>