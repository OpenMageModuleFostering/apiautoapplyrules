<?php
class Alfa9_Base_Block_System_Config_Form_Fieldset_A9base_Installed extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
  public function render(Varien_Data_Form_Element_Abstract $element)
  {
    return $this->_getHeaderHtml($element) . $this->_getFooterHtml($element);
  }
}
