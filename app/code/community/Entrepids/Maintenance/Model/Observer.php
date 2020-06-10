<?php

/**
 * @category   Entrepids
 * @package    Entrepids_Maintenance
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
class Entrepids_Maintenance_Model_Observer {

    public function maintenancePreDispatch(Varien_Event_Observer $observer) {
        //Check if is admin request
        $isAdminRequest = Mage::app()->getStore()->isAdmin();
        $isActive = Mage::getStoreConfig('maintenance/option/active');
        if ($isActive && !$isAdminRequest) {
            $request = $observer->getControllerAction()->getRequest();
            //Check bypass validation
            if (!$this->validateBypass($request)) {
                //Check if exists Nexcessnet Tupertine Module
                $moduleEnabled = Mage::helper('core')->isModuleEnabled('Nexcessnet_Turpentine');
                //Ban ESI Cache
                if ($moduleEnabled && Mage::helper('turpentine/varnish')->getVarnishEnabled()) {
                    $cache = Mage::app()->getCacheInstance();
                    $cache->banUse('turpentine_pages');
                }
                $helper = Mage::helper('maintenance');
                $cmspage = $helper->getConfig('cmspage');
                $page = Mage::getModel('cms/page')->load($cmspage);
                if (!$this->isCMSMaintenancePage($request, $page)) {
                    $storeId = Mage::app()->getStore()->getId();
                    $storesAvailable = !empty($page->getStoreId()) ? $page->getStoreId() : array();
                    $pageAvailable = (in_array(0, $storesAvailable) || in_array($storeId, $storesAvailable)) ? true : false;
                    if ($pageAvailable && $page->getIsActive()) {
                        //Go to defined CMS Error Page
                        $request->initForward()
                                ->setControllerName('page')
                                ->setModuleName('cms')
                                ->setActionName('view')
                                ->setParam('page_id', $page->getId())
                                ->setDispatched(false);
                        return;
                    } else {
                        //Not exists or not availiable defined maintenance page, go to Error 503 Magento Default Page
                        require_once Mage::getBaseDir() . DS . 'errors' . DS . 'processor.php';
                        $action = $observer->getEvent()->getControllerAction();
                        $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                        $processor = new Error_Processor();
                        $processor->process503();
                    }
                }
                //Maintenance CMS Page 
                return;
            }
            //Bypass allowed
            return;
        }
        //Maintenance mode disable or Admin request
        return;
    }

    public function validateBypass($request) {
        $helper = Mage::helper('maintenance');
        $valck = $helper->validateCookie();
        $params = $request->getParams();
        if ($valck) {
            return true;
        } else if ($helper->validateBypass($params)) {
            //You have access to Store with cookie bypass
            $helper->setCookie();
            return true;
        } else {
            return false;
        }
    }

    public function isCMSMaintenancePage($request, $page) {
        $params = $request->getParams();
        $controllerName = $request->getControllerName();
        $controllerRoutename = $request->getRouteName();
        $paramPageId = isset($params['page_id']) ? intval($params['page_id']) : -1;
        if ($controllerName == 'page' && $controllerRoutename == 'cms' && $paramPageId == $page->getId()) {
            return true;
        } else {
            return false;
        }
    }

    public function checkVarnishTurpentine() {
        //Check if Nexcessnet Turpentine module exists and is enable
        $moduleEnabled = Mage::helper('core')->isModuleEnabled('Nexcessnet_Turpentine');
        $isActive = Mage::getStoreConfig('maintenance/option/active');
        if ($isActive && $moduleEnabled && Mage::helper('turpentine/varnish')->getVarnishEnabled()) {
            //Clear varnish cache
            Mage::getModel('turpentine/varnish_admin')->flushAll();
            $model = Mage::getModel('core/cache');
            $options = $model->canUse();
            if (isset($options['turpentine_pages'])) {
                $options['turpentine_pages'] = 0;
                $model->saveOptions($options);
            }
        } else if ($moduleEnabled) { //Maintenance mode is disable but Turpentine exists
            //Clear varnish cache
            Mage::getModel('turpentine/varnish_admin')->flushAll();
            $model = Mage::getModel('core/cache');
            $options = $model->canUse();
            if (isset($options['turpentine_pages'])) {
                $options['turpentine_pages'] = 1;
                $model->saveOptions($options);
            }
        }
    }

}
