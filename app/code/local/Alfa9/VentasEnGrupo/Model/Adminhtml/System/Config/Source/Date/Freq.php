<?php
class Alfa9_VentasEnGrupo_Model_Adminhtml_System_Config_Source_Date_Freq
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=> Mage::helper('a9veg')->__('Ninguna')),
            array('value' => 1, 'label'=> Mage::helper('a9veg')->__('Semanal')),
            array('value' => 2, 'label'=> Mage::helper('a9veg')->__('Quincenal')),
            array('value' => 3, 'label'=> Mage::helper('a9veg')->__('Mensual')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            0 => Mage::helper('a9veg')->__('Ninguna'),
            1 => Mage::helper('a9veg')->__('Semanal'),
            2 => Mage::helper('a9veg')->__('Quincenal'),
            3 => Mage::helper('a9veg')->__('Mensual'),
        );
    }

    public function toArrayFlip($key = NULL) {
      if (is_null($key))
        return array_flip($this->toArray());
      else {
        $array = $this->toArrayFlip();
        return $array[$key];
      }
    }

}
