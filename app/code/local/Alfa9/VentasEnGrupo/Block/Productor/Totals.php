<?php
/**
 * Order Observer Model
 *
 * @category    Model
 * @package     Alfa9
 * @author      ctubio <ctubio@alfa9.com>
 */

class Alfa9_VentasEnGrupo_Block_Productor_Totals extends Mage_Core_Block_Template
{
    protected $totals = array();

    public function getTotals($customerId = NULL) {
        $customerId = $this->fixCustomerId($customerId);
        if (!$customerId) {
            return array();
        }
        if (!isset($this->totals[$customerId])) {
            $this->totals[$customerId] = Mage::helper('a9veg')->collectPartnerSales($customerId);
        }
        return $this->totals[$customerId];
    }

    public function getTotal($customerId = NULL) {
        $customerId = $this->fixCustomerId($customerId);
        if (!$customerId) {
            return 0;
        }
        $totals = $this->getTotals($customerId);
        return $totals['total'];
    }

    public function getMinimumOrderType($customerId = NULL) {
        $customerId = $this->fixCustomerId($customerId);
        if (!$customerId) {
            return 0;
        }

        return Mage::getSingleton('core/resource')
                ->getConnection('core_write')
                ->query("select minimumordertype from ".Mage::getConfig()->getTablePrefix()."marketplacepartner_entity_saleperpartner where mageuserid = $customerId")
                ->fetchColumn();
    }

    public function getSold($customerId = NULL) {
        $customerId = $this->fixCustomerId($customerId);
        if (!$customerId) {
            return 0;
        }
        $totals = $this->getTotals($customerId);
        return $totals['sold'];
    }

    private function fixCustomerId($customerId = NULL) {
        if (!$customerId and isset($_GET["id"])) {
            $customerId = $_GET["id"];
        }
        return $customerId;
    }
}
?>
