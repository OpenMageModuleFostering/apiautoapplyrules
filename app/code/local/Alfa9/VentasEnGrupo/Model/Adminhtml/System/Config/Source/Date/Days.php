<?php
class Alfa9_VentasEnGrupo_Model_Adminhtml_System_Config_Source_Date_Days
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=> Mage::helper('a9veg')->__('Lunes')),
            array('value' => 2, 'label'=> Mage::helper('a9veg')->__('Martes')),
            array('value' => 3, 'label'=> Mage::helper('a9veg')->__('Miercoles')),
            array('value' => 4, 'label'=> Mage::helper('a9veg')->__('Jueves')),
            array('value' => 5, 'label'=> Mage::helper('a9veg')->__('Viernes')),
            array('value' => 6, 'label'=> Mage::helper('a9veg')->__('Sabado')),
            array('value' => 7, 'label'=> Mage::helper('a9veg')->__('Domingo')),
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
            1 => Mage::helper('a9veg')->__('Lunes'),
            2 => Mage::helper('a9veg')->__('Martes'),
            3 => Mage::helper('a9veg')->__('Miercoles'),
            4 => Mage::helper('a9veg')->__('Jueves'),
            5 => Mage::helper('a9veg')->__('Viernes'),
            6 => Mage::helper('a9veg')->__('Sabado'),
            7 => Mage::helper('a9veg')->__('Domingo'),
        );
    }

}
