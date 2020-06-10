<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Payment
 * @copyright Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Base container block for payment methods forms
 *
 * @method Mage_Sales_Model_Quote getQuote()
 *
 * @category   Mage
 * @package    Mage_Payment
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Payment_Block_Form_Container extends Mage_Core_Block_Template
{
    /**
     * Prepare children blocks
     */
    protected function _prepareLayout()
    {
        /**
         * Create child blocks for payment methods forms
         */
        foreach ($this->getMethods() as $method) {
            $this->setChild(
               'payment.method.'.$method->getCode(),
               $this->helper('payment')->getMethodFormBlock($method)
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Check payment method model
     *
     * @param Mage_Payment_Model_Method_Abstract $method
     * @return bool
     */
    protected function _canUseMethod($method)
    {

        if( !$this->userUseMethod( $method ) ){ return false; }

        return $method->isApplicableToQuote($this->getQuote(), 
              Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
            | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
            | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
        );
    }

    /**
     * Check and prepare payment method model
     *
     * Redeclare this method in child classes for declaring method info instance
     *
     * @param Mage_Payment_Model_Method_Abstract $method
     * @return bool
     */
    protected function _assignMethod($method)
    {
        $method->setInfoInstance($this->getQuote()->getPayment());
        return $this;
    }

    /**
     * Declare template for payment method form block
     *
     * @param   string $method
     * @param   string $template
     * @return  Mage_Payment_Block_Form_Container
     */
    public function setMethodFormTemplate($method='', $template='')
    {
        if (!empty($method) && !empty($template)) {
            if ($block = $this->getChild('payment.method.'.$method)) {
                $block->setTemplate($template);
            }
        }
        return $this;
    }

    /**
     * Retrieve available payment methods
     *
     * @return array
     */
    public function getMethods()
    {
        $methods = $this->getData('methods');
        if ($methods === null) {
            $quote = $this->getQuote();
            $store = $quote ? $quote->getStoreId() : null;
            $methods = array();
            foreach ($this->helper('payment')->getStoreMethods($store, $quote) as $method) {
                if ($this->_canUseMethod($method) && $method->isApplicableToQuote(
                    $quote,
                    Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL
                )) {
                    $this->_assignMethod($method);
                    $methods[] = $method;
                }
            }
            $this->setData('methods', $methods);
        }

        return $methods;
    }

    /**
     * Retrieve code of current payment method
     *
     * @return mixed
     */
    public function getSelectedMethodCode()
    {
        $methods = $this->getMethods();
        if (!empty($methods)) {
            reset($methods);
            return current($methods)->getCode();
        }
        return false;
    }

    /**
     * code for rmorales@mlg.com.mx
     * 
     * @param Mage_Payment_Model_Method_Abstract $method
     * @return bool
     */
    public function userUseMethod( $method ){

        $this->getAccessPaymentMethod();
        $u = $this->getUserForAcces();

        $m = $method->getCode();
        $s = $m;

        $status = true;
        switch( $m ){
            // openpay tiendas
            case 'stores':
            // openpay spai
            case 'banks':
            // transferencia interbancaria
            case 'banktransfer':
            // pago a contraentrega
            case 'cashondelivery':
            // openpay tarjeta de credito
            case 'charges':
                $status = $this->validAccessPaymentMethod( $m,$u );
                break;
        }

        if( $status == true ){
            $s .= ' ==> permited'; }else{
            $s .= ' ==> blocked';
        }
        Mage::log("access payment method ==> $s", Zend_Log::INFO, 'payment_method.log');        

        return $status;
    }

    /**
     * code for rmorales@mlg.com.mx
     * carga o lee los permisos y los almacena en $_SESSION['payment_method_access']
     * 
     * @return bool
     */
    public function getAccessPaymentMethod(){
        // lee permisos
            //Mage::log(" ==> leyendo permisos", Zend_Log::INFO, 'payment_method.log');
            $ca = null;
            $ca = Mage::getModel('core/variable')->loadByCode('payment_method_access')->getValue('plain');
            $md5 = md5( $ca );

            if( $ca == null || $ca == '' ){
                Mage::log(" ==> sin restricciones", Zend_Log::INFO, 'payment_method.log');
                return false;
            }

        // determinando si hay restricciones cargadas
            if( !isset( $_SESSION['payment_method_access_cntl'] ) ){
                $_SESSION['payment_method_access_cntl'] = null;
                $_SESSION['payment_method_access'] = null;
            }

            if( $_SESSION['payment_method_access'] == null ){
                $_SESSION['payment_method_access_cntl'] = null;
            }

            if( $_SESSION['payment_method_access_cntl'] == $md5 ){
                //$s = " ==> permisos ==> [".print_r( $_SESSION['payment_method_access'],true )."]";
                //Mage::log($s, Zend_Log::INFO, 'payment_method.log');
                return true;
            }
        // cargando permisos
            $ca = json_decode( $ca, true );
            //$s = " ==> permisos =====> [".print_r( $ca,true )."]";
            //Mage::log($s, Zend_Log::INFO, 'payment_method.log');
            if( $ca != null ){
                if( isset( $ca['payment_method'] ) ){
                    $_SESSION['payment_method_access'] = $ca['payment_method'];
                    $_SESSION['payment_method_access_cntl'] = $md5;
                    return true;
                }
            }

        Mage::log(" ==> falla al leer permisos", Zend_Log::INFO, 'payment_method.log');
        return false;
    }

    /**
     * code for rmorales@mlg.com.mx
     * obtiene datos del usuario logueado user_id, user_email, user_group_id
     * 
     * @return array
     */
    public function getUserForAcces(){
        if( !Mage::getSingleton('customer/session')->isLoggedIn() ) {
            Mage::log("user data ==> user no logued", Zend_Log::INFO, 'payment_method.log');
            return null;
        }

        $a = null;
        $a['user_id']       = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $a['user_email']    = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
        $a['user_group_id'] = Mage::getSingleton('customer/session')->getCustomerGroupId();

        //Mage::log("user data ==> ".print_r( $a,true ), Zend_Log::INFO, 'payment_method.log');

        return $a;
    }

    /**
     * code for rmorales@mlg.com.mx
     * determina si un usuario puede utilizar metodo de pago 
     * 
     * tiene 2 validaciones primarias blocked y permited
     *  cada una de ella valida grupos de usuarios
     *      con cada validacion de usuarios se validan 2 matrices
     *          user_group_all ==> paratodos los grupos permitidos o bloqueados
     *          user_group ==> para grupos_id de usuarios especificos permitido o bloqueados
     * 
     * @return bool
     */
    public function validAccessPaymentMethod( $method='',$user=null ){
        if( $user == null ){ return false; }
        if( $method == '' ){ return false; }
        if( !isset( $_SESSION['payment_method_access'] ) ){
            Mage::log(" ==> sin restricciones", Zend_Log::INFO, 'payment_method.log');
            return true;
        }

        /* validando existencia de permisos */
            if( $_SESSION['payment_method_access'] == null ){
                Mage::log(" ==> sin restricciones", Zend_Log::INFO, 'payment_method.log');
                return true;
            }

            if( !isset( $_SESSION['payment_method_access'][ $method ] ) ){
                return true;
            }

            $p = $_SESSION['payment_method_access'][ $method ];
            
            //$s = " ==> permisos [$method] ==> [".print_r( $p,true )."]";
            //Mage::log($s, Zend_Log::INFO, 'payment_method.log');

        $access = true;

        if( isset( $p['blocked'] ) ){
            if( isset( $p['blocked']['user_group_all'] ) ){
                if( $p['blocked']['user_group_all'] == 1 ){
                    $access = false;
                }
            }
            if( isset( $p['blocked']['user_group'] ) ){
                foreach ($p['blocked']['user_group'] as $et => $r) {
                    if( $user['user_group_id'] == $r ){
                        $access = false;
                    }
                }
            }
        }

        if( isset( $p['permited'] ) ){
            if( isset( $p['permited']['user_group_all'] ) ){
                if( $p['permited']['user_group_all'] == 1 ){
                    $access = true;
                }
            }
            if( isset( $p['permited']['user_group'] ) ){
                foreach ($p['permited']['user_group'] as $et => $r) {
                    if( $user['user_group_id'] == $r ){
                        $access = true;
                    }
                }
            }
        }

        //Mage::log(" ==> $method ==> ".( $access?'permited':'blocked' ), Zend_Log::INFO, 'payment_method.log');
        return $access;
    }

}
