<?php
class Alfa9_Base_Block_System_Config_Form_Fieldset_A9base_Extensions extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
  public function render(Varien_Data_Form_Element_Abstract $element)
  {
    return '<div id="' . $element->getId() . '"></div>';
  }
}
