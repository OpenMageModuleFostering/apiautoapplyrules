<?php
class Alfa9_VentasEnGrupo_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isAdminStore() {
        return (Mage::app()->getStore()->getCode()=='admin');
    }

    public function isMainStore() {
        return (Mage::app()->getStore()->getCode()=='default');
    }

    public function isGrupoConsumoStore() {
        return !$this->isAdminStore() and !$this->isMainStore();
    }

    public function getCurrentGrupoConsumo() {
        return $this->isGrupoConsumoStore() ? Mage::app()->getStore() : NULL;
    }

    public function collectPartnerSales($customerId) {
        if (!$customerId) {
            return NULL;
        }

        $sold = 0;

        $saleWeeks = Array();
        $saleWeeks[] = $this->getDailySalesBlockData()->getCurrent();

        $type = Mage::getSingleton('core/resource')
                    ->getConnection('core_write')
                    ->query("select minimumordertype from ".Mage::getConfig()->getTablePrefix()."marketplacepartner_entity_saleperpartner where mageuserid = $customerId")
                    ->fetchColumn();

        foreach($saleWeeks as $saleWeek) {
        	if(is_object($saleWeek)) {
	            if (!$type) {
	                $sold += (int)Mage::getModel('sales/order')
	                             ->getCollection()
	                             ->addFieldToFilter ('created_at', array(
	                                 "from" => $saleWeek->getBegin(),
	                                 "to"   => $saleWeek->getEnd(),
	                                 "datetime" => true))
	                             ->addFieldToFilter ('store_id', Mage::app()->getStore()->getStoreId())
	                             ->load()
	                             ->count();
	            } else {
	                $sales = Mage::getModel('sales/order')
	                             ->getCollection()
	                             ->addFieldToFilter ('created_at', array(
	                                 "from" => $saleWeek->getBegin(),
	                                 "to"   => $saleWeek->getEnd(),
	                                 "datetime" => true))
	                             ->addFieldToFilter ('store_id', Mage::app()->getStore()->getStoreId())
	                             ->load();
	                foreach($sales as $order) {
	                    if (!$order->isCanceled()) {
	                        foreach($order->getAllItems() as $item) {
	                            if ($item->getPartner() == $customerId) {
	                                $sold += $item->getPrice();
	                            }
	                        }
	                    }
	                }
	            }
        	}
        }
        return count($saleWeeks)
            ? array(
              'total' => (string)(int)Mage::getSingleton('core/resource')
                    ->getConnection('core_write')
                    ->query("select minimumorder from ".Mage::getConfig()->getTablePrefix()."marketplacepartner_entity_saleperpartner where mageuserid = $customerId")
                    ->fetchColumn(),
              'sold' => (string)$sold
            ) : NULL;
    }

    public function getDailySalesBlockData($store = NULL) {
        // Evita problemas de timezone (UTC vs Local)
        date_default_timezone_set(Mage::app()->getStore($store)->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE));

        // Obtiene los segundos de cada fecha
        $date = time();
        $referencedate = strtotime(Mage::getStoreConfig('a9veg/etc/referencedate', $store));

        // Calcula los nº de semana (referencia 29 diciembre 1969)
        $weekRD = ceil((($referencedate - (60*60*24*3))/(60*60*24*7)) + 1);
        $weekNOW = ceil((($date - (60*60*24*3))/(60*60*24*7)) + 1);

        if (Mage::getModel('a9veg/adminhtml_system_config_source_date_freq')->toArrayFlip('Ninguna') == Mage::getStoreConfig('a9veg/etc/freq', $store)) {
            $current = NULL;
            $next = NULL;
        } else if (date('Y-m-d H:i:s', $referencedate) > date('Y-m-d H:i:s', $date)) {
            $selectedWeek = $weekRD - $weekNOW;

            // Comprueba si hubo periodo de venta en la semana de referencia o en la siguiente.
            if (Mage::getStoreConfig('a9veg/etc/start', $store) < date('N',$referencedate)) {
                $selectedWeek += 1;
            }

            $current = NULL;
            $next = $this->getStoreSaleWeek($date, $store, $selectedWeek);
        } else {
            $selectedWeek = $diffWeeks = $weekNOW - $weekRD;

            switch(Mage::getStoreConfig('a9veg/etc/freq', $store)) {
                case Mage::getModel('a9veg/adminhtml_system_config_source_date_freq')->toArrayFlip('Semanal'):
                    $selectedWeek = 0;
                    $weeksDifference = 1;
                    break;
                case Mage::getModel('a9veg/adminhtml_system_config_source_date_freq')->toArrayFlip('Quincenal'):
                    $selectedWeek = $selectedWeek % 2;
                    // Comprueba si hubo periodo de venta en la semana de referencia o en la siguiente.
                    if (Mage::getStoreConfig('a9veg/etc/start', $store) < date('N',$referencedate)) {
                        $selectedWeek += 1;
                    }
                    $weeksDifference = 2;
                    break;
                case Mage::getModel('a9veg/adminhtml_system_config_source_date_freq')->toArrayFlip('Mensual'):
                    $selectedWeek = (4 - $selectedWeek % 4) % 4;
                    // Comprueba si hubo periodo de venta en la semana de referencia o en la siguiente.
                    if (Mage::getStoreConfig('a9veg/etc/start', $store) < date('N',$referencedate)) {
                        $selectedWeek += 1;
                    }
                    $weeksDifference = 4;
                    break;
            }

            if ($this->getStoreSaleWeek($date, $store, $selectedWeek)->getBegin() <= date('Y-m-d H:i:s', $date)
            && date('Y-m-d H:i:s', $date) <= $this->getStoreSaleWeek($date, $store, $selectedWeek)->getEnd()) {
                $current = $this->getStoreSaleWeek($date, $store, $selectedWeek);
                $next = $this->getStoreSaleWeek($date, $store, $selectedWeek + $weeksDifference);
            } else {
                $current = NULL;
                $next = $this->getStoreSaleWeek($date, $store, $selectedWeek);
            }
        }

        return new Varien_Object(array(
            'active' => (bool)$current,
            'current' => $current,
            'next' => $next));
    }

    /* Deplecated */
    public function getStoreSaleWeeks($date, $store = NULL, $lastSaleWeek = 3) {
        $saleWeeks = array();
        for ($i = 0; $i <= $lastSaleWeek; $i++) {
            $saleWeeks[] = $this->getStoreSaleWeek($date, $store, $i);
        }

        return $saleWeeks;
    }

    private function getStoreSaleWeek($date, $store, $modWeek) {
        if (is_null($store)) {
            $store = Mage::app()->getStore();
        }

        $dateValue = - date('N', $date) + $modWeek * 7;

        $beginValue = Mage::getStoreConfig('a9veg/etc/start', $store) + $dateValue;
        $endValue = Mage::getStoreConfig('a9veg/etc/end', $store) + $dateValue;
        $deliveryValue = Mage::getStoreConfig('a9veg/etc/delivery', $store) + $dateValue;

        $strtotime = new Varien_Object(array(
            'begin'    => $beginValue,
            'end'      => $endValue,
            'delivery' => $deliveryValue));

        if (Mage::getStoreConfig('a9veg/etc/end', $store) < Mage::getStoreConfig('a9veg/etc/start', $store)) {
            $strtotime->setEnd($strtotime->getEnd() + 7);
            $strtotime->setDelivery($strtotime->getDelivery() + 7);
        }

        if (Mage::getStoreConfig('a9veg/etc/delivery', $store) <= Mage::getStoreConfig('a9veg/etc/end', $store)) {
            $strtotime->setDelivery($strtotime->getDelivery() + 7);
        }

        if ($strtotime->getBegin() > 0) {
            $strtotime->setBegin(strtotime(date('Y-m-d',$date).' + '.$strtotime->getBegin().' days'));
        } else {
            $strtotime->setBegin(strtotime(date('Y-m-d',$date).' - '.abs($strtotime->getBegin()).' days'));
        }

        if ($strtotime->getEnd() > 0) {
            $strtotime->setEnd(strtotime(date('Y-m-d',$date).' + '.$strtotime->getEnd().' days'));
        } else {
            $strtotime->setEnd(strtotime(date('Y-m-d',$date).' - '.abs($strtotime->getEnd()).' days'));
        }

        if ($strtotime->getDelivery() > 0) {
            $strtotime->setDelivery(strtotime(date('Y-m-d',$date).' + '.$strtotime->getDelivery().' days'));
        } else {
            $strtotime->setDelivery(strtotime(date('Y-m-d',$date).' - '.abs($strtotime->getDelivery()).' days'));
        }

        return new Varien_Object(array(
            'begin'             => date('Y-m-d', $strtotime->getBegin()) . ' 00:00:00',
            'end'               => date('Y-m-d', $strtotime->getEnd()) . ' 23:59:59',
            'delivery'          => date('Y-m-d', $strtotime->getDelivery()),
            'begin_formated'    => $this->strtr(date('l', $strtotime->getBegin())).' '.date('d', $strtotime->getBegin()).' de '.$this->strtr(date('F', $strtotime->getBegin())),
            'end_formated'      => $this->strtr(date('l', $strtotime->getEnd())).' '.date('d', $strtotime->getEnd()).' de '.$this->strtr(date('F', $strtotime->getEnd())),
            'delivery_formated' => $this->strtr(date('l', $strtotime->getDelivery())).' '.date('d', $strtotime->getDelivery()).' de '.$this->strtr(date('F', $strtotime->getDelivery())),));
    }

    public function strtr($str) {
        return strtr(
            strtr(
            $str,
            array('Monday' => 'Lunes',
                  'Tuesday' => 'Martes',
                  'Wednesday' => 'Miércoles',
                  'Thursday' => 'Jueves',
                  'Friday' => 'Viernes',
                  'Saturday' => 'Sábado',
                  'Sunday' => 'Domingo')),
            array('January' => 'Enero',
                  'February' => 'Febrero',
                  'March' => 'Marzo',
                  'April' => 'Abril',
                  'May' => 'Mayo',
                  'June' => 'Junio',
                  'July' => 'Julio',
                  'August' => 'Agosto',
                  'September' => 'Septiembre',
                  'October' => 'Octubre',
                  'November' => 'Noviembre',
                  'December' => 'Diciembre'));
    }
}
