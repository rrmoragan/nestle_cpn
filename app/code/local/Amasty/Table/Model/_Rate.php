<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */
class Amasty_Table_Model_Rate extends Mage_Core_Model_Abstract
{
    const MAX_LINE_LENGTH = 50000;
    const COL_NUMS = 19;
    const HIDDEN_COLUMNS = 2;
    const BATCH_SIZE = 50000;
    const COUNTRY = 0;
    const STATE = 1;
    const ZIP_FROM = 3;
    const NUM_ZIP_FROM = 18;
    const ZIP_TO = 4;
    const NUM_ZIP_TO = 19;
    const PRICE_TO = 6;
    const WEIGHT_TO = 8;
    const QTY_TO = 10;
    const SHIPPING_TYPE = 11;
    const ALGORITHM_SUM = 0;
    const ALGORITHM_MAX = 1;
    const ALGORITHM_MIN = 2;
    const MAX_VALUE = 99999999;

    protected $_shippingTypes = array();
    protected $_existingShippingTypes = array();
    protected $_prev_data = array();

    public function _construct()
    {
        parent::_construct();
        $this->_init('amtable/rate');
    }

    public function findBy($request, $collection)
    {
        Mage::log( 'findBy()', Zend_Log::INFO, 'envios.log');

        if (!$request->getAllItems()) {
            return array();
        }

        if($collection->getSize() == 0)
        {
            return array();
        }

        $methodIds = array();
        foreach ($collection as $method)
        {
            $methodIds[] = $method -> getMethodId();

        }

        // calculate price and weight
        $allowFreePromo = Mage::getStoreConfig('carriers/amtable/allow_promo');
        $ignoreVirtual   = Mage::getStoreConfig('carriers/amtable/ignore_virtual');

        $configurableSetting = Mage::getStoreConfig('carriers/amtable/configurable_child');
        $bundleSetting = Mage::getStoreConfig('carriers/amtable/bundle_child');

        $items = $request->getAllItems();

        $collectedTypes = array();

        foreach($items as $item)
        {

            $typeId = $item->getProduct()->getTypeId();
            $shipmentType = $item->getProduct()->getShipmentType();

            if ($item->getParentItemId())
                continue;

            if (($item->getHasChildren() && $typeId == 'configurable' && $configurableSetting == '0') ||
                ($item->getHasChildren() && $typeId == 'bundle' && $bundleSetting == '2') ||
                ($item->getHasChildren() && $typeId == 'bundle' && $bundleSetting == '0' && $shipmentType == '1'))
            {
                foreach ($item->getChildren() as $child) {
                    $this->getShippingTypes($child);
                }

            } else {
                $this->getShippingTypes($item);
            }
        }

        $this->_shippingTypes = $this->_existingShippingTypes;
        $this->_shippingTypes[] = 0;

        $this->_shippingTypes = array_unique($this->_shippingTypes);
        $this->_existingShippingTypes = array_unique($this->_existingShippingTypes);

        $allCosts = array();

        $allRates = $this->getResourceCollection();
        $allRates->addMethodFilters($methodIds);
        $ratesTypes = array();

        foreach ($allRates as $singleRate){
            $ratesTypes[$singleRate->getMethodId()][]= $singleRate->getShippingType();
        }

        $comisiones = array();
        $intersectTypes = array();

        foreach ($ratesTypes as $key => $value){

            $intersectTypes[$key] = array_intersect($this->_shippingTypes,$value);
            arsort($intersectTypes[$key]);
            $methodIds = array($key);
            $allTotals =  $this->calculateTotals($request, $ignoreVirtual, $allowFreePromo,'0');

            foreach ($intersectTypes[$key] as $shippingType){

                $totals = $this->calculateTotals($request, $ignoreVirtual, $allowFreePromo,$shippingType);
                $comisiones[] = $totals;

                if ($allTotals['qty'] > 0 && (!Mage::getStoreConfig('carriers/amtable/dont_split') || $allTotals['qty'] == $totals['qty'])) {

                    if ($shippingType == 0)
                        $totals = $allTotals;

                    $allTotals['not_free_price'] -= $totals['not_free_price'];
                    $allTotals['not_free_weight'] -= $totals['not_free_weight'];
                    $allTotals['not_free_qty'] -= $totals['not_free_qty'];
                    $allTotals['qty'] -= $totals['qty'];

                    $allRates = $this->getResourceCollection();
                    $allRates->addAddressFilters($request);
                    $allRates->addTotalsFilters($totals,$shippingType);
                    $allRates->addMethodFilters($methodIds);
                    foreach($this->calculateCosts($allRates, $totals, $request,$shippingType) as $key => $cost){

                        if (($totals['not_free_qty'] < 1) && ($totals['qty'] < 1))
                            continue;

                        if ($totals['not_free_qty'] < 1)
                            $cost['cost'] = 0;

                        $method = Mage::getModel('amtable/method')->load($key);
                        if (empty($allCosts[$key])){
                            $allCosts[$key]['cost'] = $cost['cost'];
                            $allCosts[$key]['time'] = $cost['time'];
                        }
                        else {
                            switch ($method->getSelectRate()) {
                                case self::ALGORITHM_MAX:
                                    if ($allCosts[$key]['cost'] < $cost['cost']) {
                                        $allCosts[$key]['cost'] = $cost['cost'];
                                        $allCosts[$key]['time'] = $cost['time'];
                                    }
                                    break;
                                case self::ALGORITHM_MIN:
                                    if ($allCosts[$key]['cost'] > $cost['cost']) {
                                        $allCosts[$key]['cost'] = $cost['cost'];
                                        $allCosts[$key]['time'] = $cost['time'];
                                    }
                                    break;
                                default:
                                    $allCosts[$key]['cost'] += $cost['cost'];
                                    $allCosts[$key]['time'] = $cost['time'];
                            }
                        }

                        $collectedTypes[$key][] = $shippingType;
                        $freeTypes[$key]= $method->getFreeTypes();
                    }
                }
            }
        }

        //do not show method if quote has "unsuitable" items
        foreach ($allCosts as $key => $cost){
            //1.if the method contains rate with type == All
            if (in_array('0',$collectedTypes[$key]))
                continue;
            //2.if every item in quote has applicable rates in the method
            $extraTypes = array_diff($this->_existingShippingTypes,$collectedTypes[$key]);
            if (!$extraTypes)
                continue;
            //3.if every item has (2) OR is in method's free_types
            if (!array_diff($extraTypes,$freeTypes[$key]))
                continue;

            //else — do not show the method;
            unset($allCosts[$key]);
        }

        $minRates = Mage::getModel('amtable/method')->getCollection()->hashMinRate();
        $maxRates = Mage::getModel('amtable/method')->getCollection()->hashMaxRate();

        foreach ($allCosts as $key  => $rate){
            if ($maxRates[$key] != '0.00' && $maxRates[$key] < $rate['cost']){
                $allCosts[$key]['cost'] = $maxRates[$key];
            }

            if ($minRates[$key] != '0.00' && $minRates[$key] > $rate['cost']){
                $allCosts[$key]['cost'] = $minRates[$key];
            }
        }


        $allCosts = $this->calc_comision( $allCosts, $comisiones, $request->getAllItems() );

        return $allCosts;
    }

    protected function getShippingTypes($item)
    {
        Mage::log( 'getShippingTypes()', Zend_Log::INFO, 'envios.log');

        $product = Mage::getModel('catalog/product')->load($item->getProduct()->getEntityId());
        if ($product->getAmShippingType()){
            $this->_existingShippingTypes[] = $product->getAmShippingType();
        } else {
            $this->_existingShippingTypes[] = 0;
        }
    }

    protected function calculateCosts($allRates, $totals, $request,$shippingType)
    {
        Mage::log( 'calculateCosts()', Zend_Log::INFO, 'envios.log');

        $shippingFlatParams  =  array('country', 'state', 'city');
        $shippingRangeParams =  array('price', 'qty', 'weight');

        $minCounts = array();   // min empty values counts per method
        $results   = array();
        foreach ($allRates as $rate){

            $rate = $rate->getData();

            $emptyValuesCount = 0;

            if(empty($rate['shipping_type'])){
                $emptyValuesCount++;
            }

            foreach ($shippingFlatParams as $param){
                if (empty($rate[$param])){
                    $emptyValuesCount++;
                }
            }

            foreach ($shippingRangeParams as $param) {
                if ((ceil($rate[$param . '_from']) == 0) && (ceil($rate[$param . '_to']) == self::MAX_VALUE)) {
                    $emptyValuesCount++;
                }
            }

            if (empty($rate['zip_from']) && empty($rate['zip_to']) ){
                $emptyValuesCount++;
            }

            if (!$totals['not_free_price'] && !$totals['not_free_qty'] && !$totals['not_free_weight']){
                $cost = 0;
            }
            else {
                $cost =  $rate['cost_base'] +  $totals['not_free_price'] * $rate['cost_percent'] / 100 + $totals['not_free_qty'] * $rate['cost_product'] + $totals['not_free_weight'] * $rate['cost_weight'];
            }

            $id   = $rate['method_id'];
            if ((empty($minCounts[$id]) && empty($results[$id])) || ($minCounts[$id] > $emptyValuesCount) || (($minCounts[$id] == $emptyValuesCount) && ($cost > $results[$id]))){
                $minCounts[$id] = $emptyValuesCount;
                $results[$id]['cost']   =  $cost;
                $results[$id]['time']   =  $rate['time_delivery'];
            }

        }

        return $results;
    }

    protected function calculateTotals($request, $ignoreVirtual, $allowFreePromo,$shippingType)
    {
        Mage::log( 'calculateTotals()', Zend_Log::INFO, 'envios.log');

        $totals = $this->initTotals();
        $weightModel = Mage::getModel('amtable/weight');
        $newItems = array();

        //reload child items

        $isCalculateLater = array();
        $configurableSetting = Mage::getStoreConfig('carriers/amtable/configurable_child');
        $bundleSetting = Mage::getStoreConfig('carriers/amtable/bundle_child');

        foreach ($request->getAllItems() as $item) {


            if ($item->getParentItemId())
               continue;

            if ($ignoreVirtual && $item->getProduct()->isVirtual())
                continue;

            $typeId = $item->getProduct()->getTypeId();
            $shipmentType = $item->getProduct()->getShipmentType();

            if (($item->getHasChildren() && $typeId == 'configurable' && $configurableSetting == '0') ||
                ($item->getHasChildren() && $typeId == 'bundle' && $bundleSetting == '2') ||
                ($item->getHasChildren() && $typeId == 'bundle' && $bundleSetting == '0' && $shipmentType == '1')){


                 $qty = 0;
                 $notFreeQty =0;
                 $price = 0;
                 $weight = 0;
                 $itemQty = 0;

                $flagOfPersist = false;
                foreach ($item->getChildren() as $child) {
                    $flagOfPersist = false;
                    $product = Mage::getModel('catalog/product')->load($child->getProduct()->getEntityId());

                    if (($product->getAmShippingType() != $shippingType) && ($shippingType != 0))
                        continue;


                    $flagOfPersist = true;
                    $itemQty = $child->getQty() * $item->getQty();
                    $qty        +=  $itemQty ;
                    $notFreeQty += ($itemQty - $this->getFreeQty($child, $allowFreePromo));
                    $price  += $child->getPrice() * $itemQty;
                    $weight += $weightModel->getItemWeight($child) * $itemQty;
                    $totals['tax_amount']       += $child->getBaseTaxAmount() + $child->getBaseHiddenTaxAmount();
                    $totals['discount_amount']  += $child->getBaseDiscountAmount();
                }

                Mage::log( 'product ==> '.print_r($item,true), Zend_Log::INFO, 'envios.log');

                if ($typeId == 'bundle'){
                  //  if ($flagOfPersist == false)
                   //     continue;
                  //  $qty        = $item->getQty();

                    if ($item->getProduct()->getWeightType() == 1){
                        $weight = $weightModel->getItemWeight($item);
                    }

                    if ($item->getProduct()->getPriceType() == 1){
                        $price   = $item->getPrice();
                    }

                    if ($item->getProduct()->getSkuType() == 1){
                        $totals['tax_amount']       += $item->getBaseTaxAmount() + $item->getBaseHiddenTaxAmount();
                        $totals['discount_amount']  += $item->getBaseDiscountAmount();
                    }

                    $notFreeQty = ($qty - $this->getFreeQty($item, $allowFreePromo));
                    $totals['qty']              += $qty;
                    $totals['not_free_qty']     += $notFreeQty;
                    $totals['not_free_price'] += $price;
                    $totals['not_free_weight'] += $weight;

                }elseif ($typeId == 'configurable'){

                    if ($flagOfPersist == false)
                        continue;


                    $qty     = $item->getQty();
                    $price   = $item->getPrice();
                    $weight  = $weightModel->getItemWeight($item);
                    $notFreeQty = ($qty - $this->getFreeQty($item, $allowFreePromo));
                    $totals['qty']              += $qty;
                    $totals['not_free_qty']     += $notFreeQty;
                    $totals['not_free_price'] += $price * $notFreeQty;
                    $totals['not_free_weight'] += $weight * $notFreeQty;
                    $totals['tax_amount']       += $item->getBaseTaxAmount() + $item->getBaseHiddenTaxAmount();
                    $totals['discount_amount']  += $item->getBaseDiscountAmount();

                } else { // for grouped and custom not simple products
                    $qty     = $item->getQty();
                    $price   = $item->getPrice();
                    $weight  =  $weightModel->getItemWeight($item);
                    $notFreeQty = ($qty - $this->getFreeQty($item, $allowFreePromo));
                    $totals['qty']              += $qty;
                    $totals['not_free_qty']     += $notFreeQty;
                    $totals['not_free_price'] += $price * $notFreeQty;
                    $totals['not_free_weight'] += $weight * $notFreeQty;
                }

            } else {
                $product = Mage::getModel('catalog/product')->load($item->getProduct()->getEntityId());
                if (($product->getAmShippingType() != $shippingType) && ($shippingType != 0))
                    continue;

                if ($item->getParentItemId())
                    continue;


                $qty        = $item->getQty();
                $notFreeQty = ($qty - $this->getFreeQty($item, $allowFreePromo));
                $totals['not_free_price'] += $item->getBasePrice() * $notFreeQty;
                $totals['not_free_weight'] += $weightModel->getItemWeight($item) * $notFreeQty;
                $totals['qty']              += $qty;
                $totals['not_free_qty']     += $notFreeQty;
                $totals['tax_amount']       += $item->getBaseTaxAmount() + $item->getBaseHiddenTaxAmount();
                $totals['discount_amount']  += $item->getBaseDiscountAmount();
            }

            /* rmorales almacena la cantidad pedida de los productos */
            $__getEntityId = $item->getProduct()->getEntityId();
            $this->_prev_data[ $__getEntityId ]['getEntityId'] = $__getEntityId;
            $this->_prev_data[ $__getEntityId ]['qty'] = $item->getQty();
            /* fin rmorales */

        }// foreach

        // fix magento bug
        if ($totals['qty'] != $totals['not_free_qty'])
            $request->setFreeShipping(false);

        $afterDiscount = Mage::getStoreConfig('carriers/amtable/after_discount');
        $includingTax =  Mage::getStoreConfig('carriers/amtable/including_tax');

        if ($afterDiscount)
            $totals['not_free_price'] -= $totals['discount_amount'];

        if($includingTax)
            $totals['not_free_price'] += $totals['tax_amount'];

        if ($totals['not_free_price'] < 0)
            $totals['not_free_price'] = 0;

        if ($request->getFreeShipping() && $allowFreePromo)
            $totals['not_free_price'] = $totals['not_free_weight'] = $totals['not_free_qty'] = 0;

        foreach ($totals as $key => $value){
            $totals[$key] = round($value,2);
        }


        return $totals;
    }

    public function getFreeQty($item, $allowFreePromo)
    {
        Mage::log( 'getFreeQty()', Zend_Log::INFO, 'envios.log');

        $freeQty = 0;
        if ($item->getFreeShipping() && $allowFreePromo) {
    	    $freeQty = $item->getQty();
    	}

        return $freeQty;
    }

    public function import($methodId, $fileName)
    {
        Mage::log( 'import()', Zend_Log::INFO, 'envios.log');

        $err = array();

        $fp = fopen($fileName, 'r');
        if (!$fp){
            $err[] = Mage::helper('amtable')->__('Can not open file %s .', $fileName);
            return $err;
        }
        $methodId = intval($methodId);
        if (!$methodId){
            $err[] = Mage::helper('amtable')->__('Specify a valid method ID.');
            return $err;
        }

        $countryCodes = $this->getCountries();
        $stateCodes   = $this->getStates();
        $countryNames = $this->getCountriesName();
        $stateNames   = $this->getStatesName();
        $typeLabels   = Mage::helper('amtable')->getTypes();

        $data = array();
        $dataIndex = 0;

        $currLineNum  = 0;
        while (($line = fgetcsv($fp, self::MAX_LINE_LENGTH, ',', '"')) !== false) {

            $currLineNum++;

            if ($currLineNum == 1 && $line[0] == 'Country')
            {
                continue;
            }

            if ((count($line) + self::HIDDEN_COLUMNS) != self::COL_NUMS){
               $err[] = 'Line #' . $currLineNum . ': warning, expected number of columns is ' . self::COL_NUMS;
               if (count($line) > self::COL_NUMS)
               {
                   for ($i = 0; $i < count($line) - self::COL_NUMS; $i++){
                        unset($line[self::COL_NUMS + $i]);
                   }
               }

                if (count($line) < self::COL_NUMS)
                {
                    for ($i = 0; $i <  self::COL_NUMS - count($line); $i++){
                        $line[count($line) + $i] = 0;
                    }
                }
            }

            $dataZipFrom = Mage::helper('amtable')->getDataFromZip($line[self::ZIP_FROM]);
            $dataZipTo = Mage::helper('amtable')->getDataFromZip($line[self::ZIP_TO]);
            $line[self::NUM_ZIP_FROM] = $dataZipFrom['district'];
            $line[self::NUM_ZIP_TO] = $dataZipTo['district'];

            for ($i = 0; $i < self::COL_NUMS - self::HIDDEN_COLUMNS; $i++) {
               $line[$i] = str_replace(array("\r", "\n", "\t", "\\" ,'"', "'", "*"), '', $line[$i]);
                if ($i != self::COL_NUMS - self::HIDDEN_COLUMNS - 1 && preg_match('/^([0-9]+?\,)+?[0-9]+?\.[0-9]+$/', $line[$i])) {
                    $line[$i] = str_replace(",", '', $line[$i]);
                }
            }

            $countries = array('');
            if ($line[self::COUNTRY]){
                $countries = explode(',', $line[self::COUNTRY]);
            } else {
                $line[self::COUNTRY] = '0';
            }
            $states = array('');
            if ($line[self::STATE]){
                $states = explode(',', $line[self::STATE]);
            }

            $types = array('');
            if ($line[self::SHIPPING_TYPE]){
                $types = explode(',', $line[self::SHIPPING_TYPE]);
            }

            $zips = array('');
            if ($line[self::ZIP_FROM]){
                $zips = explode(',', $line[self::ZIP_FROM]);
            }

            if(!$line[self::PRICE_TO]) $line[self::PRICE_TO] =  self::MAX_VALUE;
            if(!$line[self::WEIGHT_TO]) $line[self::WEIGHT_TO] =  self::MAX_VALUE;
            if(!$line[self::QTY_TO]) $line[self::QTY_TO] =  self::MAX_VALUE;

            foreach ($types as $type){
               if ($type == 'All'){
                    $type = 0;
                }
                if ($type && empty($typeLabels[$type])) {
                    if (in_array($type, $typeLabels)){
                        $typeLabels[$type] = array_search($type, $typeLabels);
                    }  else {
                        $err[] = 'Line #' . $currLineNum . ': invalid type code ' . $type;
                        continue;
                    }

                }
                $line[self::SHIPPING_TYPE] = $type ? $typeLabels[$type] : '';
            }

            foreach ($countries as $country){
                $country = html_entity_decode($country);
                if ($country == 'All'){
                    $country = 0;
                }

                if ($country && empty($countryCodes[$country])) {
                    if (in_array($country, $countryNames)){
                        $countryCodes[$country] = array_search($country, $countryNames);
                    }  else {
                        $err[] = 'Line #' . $currLineNum . ': invalid country code ' . $country;
                        continue;
                    }

                }
                $line[self::COUNTRY] = $country ? $countryCodes[$country] : '0';

                foreach ($states as $state){

                    if ($state == 'All'){
                        $state = '';
                    }

                    if ($state && empty($stateCodes[$state][$country])) {
                        if (in_array($state, $stateNames)){
                            $stateCodes[$state][$country] = array_search($state, $stateNames);
                        } else {
                            $err[] = 'Line #' . $currLineNum . ': invalid state code ' . $state;
                            continue;
                        }

                    }
                    $line[self::STATE] = $state ? $stateCodes[$state][$country] : '';

                    foreach ($zips as $zip){
                        $line[self::ZIP_FROM] = $zip;


                        $data[$dataIndex] = $line;
                        $dataIndex++;

                        if ($dataIndex > self::BATCH_SIZE){
                            $err = $this->returnErrors($data, $methodId,$currLineNum, $err);
                            $data = array();
                            $dataIndex = 0;
                        }
                    }
                }// states  
            }// countries 
        } // end while read  
        fclose($fp);

        if ($dataIndex){
            $err = $this->returnErrors($data, $methodId,$currLineNum, $err);
        }

        return $err;
    }


    public function returnErrors($data, $methodId,$currLineNum, $err)
    {
        Mage::log( 'returnErrors()', Zend_Log::INFO, 'envios.log');

        $errText = $this->getResource()->batchInsert($methodId, $data);

        if ($errText){
            foreach ($data as $key => $value){
                $newData[$key] = array_slice($value, 0, 12);
                $oldData[$key] = array_slice($value,12);
            }

            foreach ($newData AS $key => $arrNewData) {
                $newData[$key] = serialize($arrNewData);
            }
            $newData = array_unique($newData);
            foreach ($newData AS $key => $strNewData) {
                $newData[$key] = unserialize($strNewData);
            }

            $checkedData = array();
            foreach ($newData as $key => $value){
                $checkedData[] = array_merge($value,$oldData[$key]);
            }

            $errText = $this->getResource()->batchInsert($methodId, $checkedData);
            if ($errText){
                $err[] = 'Line #' . $currLineNum . ': duplicated conditions before this line have been skipped';
            } else {
                $err[] = 'Your csv file has been automatically cleared of duplicates and successfully uploaded';
            }
        }

        return $err;
    }

    public function getCountries()
    {
        Mage::log( 'getCountries()', Zend_Log::INFO, 'envios.log');

        $hash = array();

        $collection = Mage::getResourceModel('directory/country_collection');
        foreach ($collection as $item){
            $hash[$item->getIso3Code()] = $item->getCountryId();
            $hash[$item->getIso2Code()] = $item->getCountryId();
        }

        return $hash;
    }

    public function getStates()
    {
        Mage::log( 'getStates()', Zend_Log::INFO, 'envios.log');

        $hash = array();

        $collection = Mage::getResourceModel('directory/region_collection');
        foreach ($collection as $state){
            $hash[$state->getCode()][$state->getCountryId()] = $state->getRegionId();
        }

        return $hash;
    }

    public function getCountriesName()
    {
        Mage::log( 'getCountriesName()', Zend_Log::INFO, 'envios.log');

        $hash = array();
        $collection = Mage::getResourceModel('directory/country_collection');
        foreach ($collection as $item){
            $country_name=Mage::app()->getLocale()->getCountryTranslation($item->getIso2Code());
            $hash[$item->getCountryId()] = str_replace("'",'',$country_name);

        }
        return $hash;
    }

    public function getStatesName()
    {
        Mage::log( 'getStatesName()', Zend_Log::INFO, 'envios.log');

        $hash = array();

        $collection = Mage::getResourceModel('directory/region_collection');
        $countryHash = $this->getCountriesName();
        foreach ($collection as $state){
            $string = $countryHash[$state->getCountryId()].'/'.$state->getDefaultName();
            $hash[$state->getRegionId()] =  str_replace("'",'',$string);
        }
        return $hash;
    }

    public function initTotals()
    {
        Mage::log( 'initTotals()', Zend_Log::INFO, 'envios.log');

        $totals = array(
            'not_free_price'     => 0,
            'not_free_weight'    => 0,
            'qty'                => 0,
            'not_free_qty'       => 0,
            'tax_amount'         => 0,
            'discount_amount'    => 0,
        );
        return $totals;
    }

    public function deleteBy($methodId)
    {
        Mage::log( 'deleteBy()', Zend_Log::INFO, 'envios.log');

        return $this->getResource()->deleteBy($methodId);
    }

    /* modif rmorales@mlg.com.mx */

    // obtiene las comiciones openpay
    public function openpay_comision(){
        $openpay = Mage::getModel('core/variable')->loadByCode('openpay_comiciones')->getValue('plain');
        if( $openpay == null ){ return null; }

        $openpay = json_decode( $openpay );
        if( $openpay == null ){ return null; }

        //Mage::log( "openpay comiciones ==> ".print_r( $openpay,true ), Zend_Log::INFO, 'envios.log');

        return $openpay;
    }

    public function calc_comision($dat=null, $subtotal_all=null, $products=null){
        Mage::log( '====> calc_comision()', Zend_Log::INFO, 'envios.log');

        // si no hay datos de envio salir
        if( $dat==null ){ return null; }
        Mage::log( 'dat ==> '.print_r($dat,true), Zend_Log::INFO, 'envios.log');
        Mage::log( 'subtotal_all ==> '.print_r($subtotal_all,true), Zend_Log::INFO, 'envios.log');

        // obtiene las comixiones openpay en 1 sola variable
        $op = $this->openpay_comision();

        // _prev_data contiene Id_producto y cantidad por producto
        //Mage::log( '_prev_data ==> '.print_r($this->_prev_data,true), Zend_Log::INFO, 'envios.log');
        $lprods = $this->_prev_data;

        $comision = Mage::getModel('core/variable')->loadByCode('articulo_comision')->getValue('plain');
        $transaccion = Mage::getModel('core/variable')->loadByCode('comision_transaccion')->getValue('plain');
        $comision = (float)$comision;
        $comision = $comision/100;

        if( !$products ){ return $dat; }

        foreach ($products as $et => $item) {
            //obtiene id_del_producto
            $pid = $item->getProduct()->getEntityId();
            if( isset( $lprods[ $pid ] ) ){
                $prod = Mage::getModel('catalog/product')->load( $pid );
                $lprods[ $pid ]['sku'] = $prod->getData('codigo_barras');
                $lprods[ $pid ]['margen'] = $prod->getData('margen');
                $lprods[ $pid ]['type_id'] = $prod->getData('type_id');
                $lprods[ $pid ]['price_type'] = $prod->getData('price_type');

                if( $lprods[ $pid ]['margen']==null || $lprods[ $pid ]['margen']==0 ){
                    $lprods[ $pid ]['margen'] = $prod->getData('margen_descuento');                        
                }

                if( $lprods[ $pid ]['margen'] == null ){ $lprods[ $pid ]['margen'] = 0; }
                if( $lprods[ $pid ]['margen'] == '' ){ $lprods[ $pid ]['margen'] = 0; }
                $lprods[ $pid ]['margen_sum'] = $lprods[ $pid ]['margen'] * $lprods[ $pid ]['qty'];
            }
        }

        $margen = 0;
        foreach ($lprods as $et => $r) {
            //Mage::log( 'prods items ==> '.print_r($r,true), Zend_Log::INFO, 'envios.log');
            $margen = $margen + $r['margen_sum'];
        }

        Mage::log( 'lprods ==> '.print_r($lprods,true), Zend_Log::INFO, 'envios.log');
        Mage::log( 'margen total ==> '.print_r($margen,true), Zend_Log::INFO, 'envios.log');

        if( $products ){
            foreach ($products as $et => $item) {

                $__getEntityId = $item->getProduct()->getEntityId();

                if( isset( $this->_prev_data[ $__getEntityId ] ) ){

                    // obtiene los datos del producto
                    $dataprod = Mage::getModel('catalog/product')->load( $__getEntityId );

                    $this->_prev_data[ $__getEntityId ]['sku'] = $dataprod->_data['codigo_barras'];
                    $this->_prev_data[ $__getEntityId ]['margen'] = 0;

                    // obtiene el margen del producto
                    if( isset( $dataprod->_data['margen'] ) ){
                        $this->_prev_data[ $__getEntityId ]['margen'] = $dataprod->_data['margen'];
                    }else{
                        if( isset($dataprod->_data['margen_descuento']) ){
                          $this->_prev_data[ $__getEntityId ]['margen'] = $dataprod->_data['margen_descuento'];
                        }                        
                    }

                    if( $dataprod->_data['type_id'] == 'bundle' && $dataprod->_data['price_type'] == 0 ){
                        //Mage::log( 'dat ==> '.print_r( $dataprod->_data,true ), Zend_Log::INFO, 'envios.log');
                        Mage::log( 'dat ==> calculo_precio y calculo_margen ==> dinamico', Zend_Log::INFO, 'envios.log');

                        $groupParentsIds = Mage::getResourceSingleton('catalog/product_link')->getParentIdsByChild($dataprod->getId(), Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED);

                        Mage::log( 'dat ==> '.print_r( $groupParentsIds,true ), Zend_Log::INFO, 'envios.log');
                    }
                }
            }
        }

        $margen_descuento = 0;
        foreach ($this->_prev_data as $et => $r) {
            if( $r['margen_descuento']==0 ){
                continue;
            }
            $n = $r['qty'] * $r['margen_descuento'];
            $margen_descuento += $n;
            $this->_prev_data[$et]['suma'] = $n;
        }
        $margen_descuento = round( $margen_descuento,2 );

        $envio = 0;
        $envio_et = '';
        foreach ($dat as $et => $r) {
            if( isset($r['cost']) ){
                $envio = $r['cost'];
                $envio_et = $et;
            }
        }
        $subtotal = 0;
        foreach ($subtotal_all as $et => $r) {
            if( isset( $r['not_free_price'] ) ){
                $subtotal = $r['not_free_price'];
            }
        }

        $ss = '';
        $ss = $ss."\n\t".'cantidad pedida ==> '.json_encode( $this->_prev_data );

        $lc = null;
        $lc = array(
            'margen' => $margen_descuento,
            'comision_porcent' => $comision,
            'transaccion' => $transaccion,
            'envio' => $envio,
            'subtotal' => $subtotal,
        );

        $saldo_favor = 0;
        $envio = $envio - $margen_descuento;
        if( $envio<0 ){
            $saldo_favor = -1*$envio;
            $envio=0;
        }
        $lc['envio_menos_margen'] = $envio;
        if( $saldo_favor>0 ){
            $lc['margen_resatnte_1'] = $saldo_favor;
        }
        
        $t = $subtotal + $envio;
        $lc['subtotal_mas_envio'] = $t;
        
        $comision = $t * $comision;
        $lc['comision'] = $comision;
        
        $comision += $transaccion;
        $lc['comision_mas_transaccion'] = $comision;
        
        $envio += $comision;
        $lc['envio_mas_comisiones'] = $envio;

        if( $saldo_favor>0 ){
            $envio = $envio - $saldo_favor;
            $lc['envio_mas_comisiones_menos_margen'] = $envio;        
            if( $envio<0 ){
                $saldo_favor = -1*$envio;
            }else{
                $saldo_favor = 0;
            }
            if( $saldo_favor>0 ){
                $lc['margen_sobrante'] = $saldo_favor;        
            }
            if( $envio<0 ){
                $envio = 0;
            }
        }
        
        $envio = round($envio,2);
        $lc['envio_total'] = $envio;    

        $ss = $ss."\n\t".'calculo ==> '.json_encode( $lc );    

        Mage::log( $ss, Zend_Log::INFO, 'envios.log');
        //Mage::log( $ss, Zend_Log::INFO, 'envios-'.time().'.log');

        $dat[ $envio_et ]['cost'] = $envio;

        return $dat;
    }

    /* fin modif */
}
