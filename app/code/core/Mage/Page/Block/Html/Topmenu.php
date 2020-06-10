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
 * @package     Mage_Page
 * @copyright Copyright (c) 2006-2017 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Top menu block
 *
 * @category    Mage
 * @package     Mage_Page
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Page_Block_Html_Topmenu extends Mage_Core_Block_Template
{
    /**
     * Top menu data tree
     *
     * @var Varien_Data_Tree_Node
     */
    protected $_menu;

    /**
     * Current entity key
     *
     * @var string|int
     */
    protected $_currentEntityKey;

    /**
     * Init top menu tree structure and cache
     */
    public function _construct()
    {
        $this->_menu = new Varien_Data_Tree_Node(array(), 'root', new Varien_Data_Tree());
        /*
        * setting cache to save the topmenu block
        */
        $this->setCacheTags(array(Mage_Catalog_Model_Category::CACHE_TAG));
        $this->setCacheLifetime(false);
    }

    /**
     * Get top menu html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @return string
     */
    public function getHtml($outermostClass = '', $childrenWrapClass = '')
    {
        Mage::dispatchEvent('page_block_html_topmenu_gethtml_before', array(
            'menu' => $this->_menu,
            'block' => $this
        ));

        $this->_menu->setOutermostClass($outermostClass);
        $this->_menu->setChildrenWrapClass($childrenWrapClass);

        if ($renderer = $this->getChild('catalog.topnav.renderer')) {
            $renderer->setMenuTree($this->_menu)->setChildrenWrapClass($childrenWrapClass);
            $html = $renderer->toHtml();
        } else {
            $html = $this->_getHtml($this->_menu, $childrenWrapClass);
        }

        Mage::dispatchEvent('page_block_html_topmenu_gethtml_after', array(
            'menu' => $this->_menu,
            'html' => $html
        ));

        return $html;
    }

    /**
     * Recursively generates top menu html from data that is specified in $menuTree
     *
     * @param Varien_Data_Tree_Node $menuTree
     * @param string $childrenWrapClass
     * @return string
     * @deprecated since 1.8.2.0 use child block catalog.topnav.renderer instead
     */
    protected function _getHtml(Varien_Data_Tree_Node $menuTree, $childrenWrapClass)
    {
        $html = '';

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = is_null($parentLevel) ? 0 : $parentLevel + 1;

        $counter = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

        foreach ($children as $child) {

            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $child->setClass($outermostClass);
            }

            $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
            $html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '><span>'
                . $this->escapeHtml($child->getName()) . '</span></a>';

            if ($child->hasChildren()) {
                if (!empty($childrenWrapClass)) {
                    $html .= '<div class="' . $childrenWrapClass . '">';
                }
                $html .= '<ul class="level' . $childLevel . '">';
                $html .= $this->_getHtml($child, $childrenWrapClass);
                $html .= '</ul>';

                if (!empty($childrenWrapClass)) {
                    $html .= '</div>';
                }
            }
            $html .= '</li>';

            $counter++;
        }

        return $html;
    }

    /**
     * Generates string with all attributes that should be present in menu item element
     *
     * @param Varien_Data_Tree_Node $item
     * @return string
     */
    protected function _getRenderedMenuItemAttributes(Varien_Data_Tree_Node $item)
    {
        $html = '';
        $attributes = $this->_getMenuItemAttributes($item);

        foreach ($attributes as $attributeName => $attributeValue) {
            $html .= ' ' . $attributeName . '="' . str_replace('"', '\"', $attributeValue) . '"';
        }

        return $html;
    }

    /**
     * Returns array of menu item's attributes
     *
     * @param Varien_Data_Tree_Node $item
     * @return array
     */
    protected function _getMenuItemAttributes(Varien_Data_Tree_Node $item)
    {
        $menuItemClasses = $this->_getMenuItemClasses($item);
        $attributes = array(
            'class' => implode(' ', $menuItemClasses)
        );

        return $attributes;
    }

    /**
     * Returns array of menu item's classes
     *
     * @param Varien_Data_Tree_Node $item
     * @return array
     */
    protected function _getMenuItemClasses(Varien_Data_Tree_Node $item)
    {
        $classes = array();

        $classes[] = 'level' . $item->getLevel();
        $classes[] = $item->getPositionClass();

        if ($item->getIsFirst()) {
            $classes[] = 'first';
        }

        if ($item->getIsActive()) {
            $classes[] = 'active';
        }

        if ($item->getIsLast()) {
            $classes[] = 'last';
        }

        if ($item->getClass()) {
            $classes[] = $item->getClass();
        }

        if ($item->hasChildren()) {
            $classes[] = 'parent';
        }

        return $classes;
    }

    /**
     * Retrieve cache key data
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $shortCacheId = array(
            'TOPMENU',
            Mage::app()->getStore()->getId(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
            Mage::getSingleton('customer/session')->getCustomerGroupId(),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            $this->getCurrentEntityKey()
        );
        $cacheId = $shortCacheId;

        $shortCacheId = array_values($shortCacheId);
        $shortCacheId = implode('|', $shortCacheId);
        $shortCacheId = md5($shortCacheId);

        $cacheId['entity_key'] = $this->getCurrentEntityKey();
        $cacheId['short_cache_id'] = $shortCacheId;

        return $cacheId;
    }

    /**
     * Retrieve current entity key
     *
     * @return int|string
     */
    public function getCurrentEntityKey()
    {
        if (null === $this->_currentEntityKey) {
            $this->_currentEntityKey = Mage::registry('current_entity_key')
                ? Mage::registry('current_entity_key') : Mage::app()->getStore()->getRootCategoryId();
        }
        return $this->_currentEntityKey;
    }

    /**
     * add code for rmorales@mlg.com.mx
     * 
     * is required defined in the system magento personalized vars whith json in text plain
     *      category_access
     *
     *  ezxample content
     *      {"category_access":{"url_categoria":{"blocked":{"user":{"type":"all"}},"permited":{"user":{"type":"list","elems":["rmorales@mlg.com.mx"]}}}}}
     *  
        array(
            "category_access" => array(

                "url_categoria" => array(
                    "blocked" => array(
                        "user" => array(
                            "type" => "all"
                        )
                    ),
                    "permited" => array(
                        "user" => array(
                            "type" => "list",
                            "elems" => array(
                                "user_email",
                                "user_id"
                            )
                        )
                    ),
                )

            )
        )

     */

    public $categoryBlocked = '';

    /**
     * add function for rmorales@mlg.com.mx
     *  determina si la opcion esta bloqueada
     *
     * @param Varien_Data_Tree_Node $url
     * @return boolean
     */
    public function _getMenuAccess( $url='' ){
        //Mage::log("_getMenuAccess()", Zend_Log::INFO, 'category_access.log');

        $this->categoryAccess();
        /* si no hay datos de category_access ==> salir */
        if( !$this->isCategdoryAccess() ){
            Mage::log("permited ====> $url", Zend_Log::INFO, 'category_access.log');
            return false;
        }

        if( $this->isBlockedCategoryAccess( $url ) ){
            Mage::log("blocked ===> $url", Zend_Log::INFO, 'category_access.log');
            return true;
        }

        Mage::log("permited ==> $url", Zend_Log::INFO, 'category_access.log');
        return false;
    }
    /**
     * add function for rmorales@mlg.com.mx
     *  carga los valores para las categorias restringidas
     * @return boolean
     */
    public function categoryAccess()    {
        //Mage::log("categoryAccess()", Zend_Log::INFO, 'category_access.log');

        // lee permisos
            Mage::log(" ==> leyendo permisos", Zend_Log::INFO, 'category_access.log');
            $ca = null;
            $ca = Mage::getModel('core/variable')->loadByCode('category_access')->getValue('plain');
            $md5 = md5( $ca );

            if( $ca == null || $ca == '' ){
                Mage::log(" ==> sin restricciones", Zend_Log::INFO, 'category_access.log');
                return false;
            }

        // determinando si hay restricciones cargadas
            Mage::log(" ==> cargando permisos", Zend_Log::INFO, 'category_access.log');
            if( !isset( $_SESSION['category_access_cntl'] ) ){
                $_SESSION['category_access_cntl'] = null;
                $_SESSION['category_access'] = null;
            }

            if( $_SESSION['category_access'] == null ){
                $_SESSION['category_access_cntl'] = null;
                Mage::log(" ==> permisos ==> null", Zend_Log::INFO, 'category_access.log');
            }
            if( $_SESSION['category_access_cntl'] == $md5 ){
                Mage::log(" ==> permisos ==> [".print_r( $_SESSION['category_access'],true )."]", Zend_Log::INFO, 'category_access.log');
                return false;
            }

        // cargando permisos
            $ca = json_decode( $ca, true );
            Mage::log(" ==> permisos =====> [".print_r( $ca,true )."]", Zend_Log::INFO, 'category_access.log');
            if( $ca != null ){
                $_SESSION['category_access'] = $ca['category_access'];
                $_SESSION['category_access_cntl'] = $md5;
                return true;
            }

        Mage::log(" ==> falla al leer permisos", Zend_Log::INFO, 'category_access.log');
        return false;
    }
    /**
     * add function for rmorales@mlg.com.mx
     *
     * @return boolean
     */
    public function isCategdoryAccess(){
        //Mage::log("isCategdoryAccess()", Zend_Log::INFO, 'category_access.log');

        if( !isset( $_SESSION['category_access'] ) ){ return false; }
        if( $_SESSION['category_access'] == null ){ return false; }
        return true;
    }
    /**
     * add function for rmorales@mlg.com.mx
     *
     * @param Varien_Data_Tree_Node $url
     * @return boolean
     */
    public function isBlockedCategoryAccess( $url = '' ){
        //Mage::log("isBlockedCategoryAccess()", Zend_Log::INFO, 'category_access.log');

        $this->categoryBlocked = '';

        /* busca la categoria en la url */
        $bc_indx = $this->searchCategdoryAccess( $url );
        //Mage::log("Category ==> $url ==> [".( ($bc_indx=='')?'not found':'found' )."]", Zend_Log::INFO, 'category_access.log');

        if( $bc_indx == '' ){
            return false;
        }

        $this->categoryBlocked = $bc_indx;        

        $blocked  = $_SESSION['category_access'][ $bc_indx ]['blocked']['user'];
        $permited = $_SESSION['category_access'][ $bc_indx ]['permited']['user'];

        $st = false;

        /* determina si la categoria esta bloqueada para todo usuario */
        if( $blocked['type'] == 'all' ){ $st = true; }
        Mage::log("rule 1 ==> bloqued all users ==> ".( $st?'true':'false' ), Zend_Log::INFO, 'category_access.log');

        $u = $this->userCategoryAccess();
        if( $u==null ){ 
            return $st;
        }

        /* busca si la categoria esta bloqueada para usuarios especificos */
        if( $blocked['type'] == 'list' ){
            if( $this->userCategoryAccessAsign( $u['user_email'], $blocked['elems'] ) ){
                $st = true;
                Mage::log("\nCategdoryAccess user ==> login\nCategdoryAccess $bc_indx user [".$u['user_email']."] ==> blocked", Zend_Log::INFO, 'category_access.log');
            }
            if( $this->userCategoryAccessAsign( $u['user_id'],    $blocked['elems'] ) ){
                $st = true;
                Mage::log("\nCategdoryAccess user ==> login\nCategdoryAccess $bc_indx user [".$u['user_id']."] ==> blocked", Zend_Log::INFO, 'category_access.log');
            }
        }

        Mage::log("rule 2 ==> bloqued this user ==> ".( $st?'true':'false' ), Zend_Log::INFO, 'category_access.log');

        /* busca si la categoria esta accesible para todos */
        if( $permited['type'] == 'all' ){ $st = false; }
        Mage::log("rule 3 ==> permited all users ==> ".( $st?'false':'true' ), Zend_Log::INFO, 'category_access.log');

        /* busca si la categoria esta accesible para usuarios especificos */
        if( $permited['type'] == 'list' ){

            if( $this->userCategoryAccessAsign( $u['user_email'], $permited['elems'] ) ){ 
                $st = false;
            }

            if( $this->userCategoryAccessAsign( $u['user_id'],    $permited['elems'] ) ){
                $st = false;
            }

            Mage::log("rule 4 ==> permited this users ==> ".( $st?'false':'true' ), Zend_Log::INFO, 'category_access.log');
        }

        $ss = 'permited';
        if( $st == true ){  $ss = 'blocked'; }
        Mage::log("CategdoryAccess ==> $ss", Zend_Log::INFO, 'category_access.log');

        return $st;
    }
    /**
     * add function for rmorales@mlg.com.mx
     *
     * @param Varien_Data_Tree_Node $url
     * @return boolean
     */
    public function searchCategdoryAccess( $url='', $v=false ){
        Mage::log("searchCategdoryAccess( $url )", Zend_Log::INFO, 'category_access.log');

        if( $url=='' ){
            if( $v ) { Mage::log("searchCategdoryAccess ==> $et ==> null", Zend_Log::INFO, 'category_access.log'); }
            return '';
        }

        foreach ($_SESSION['category_access'] as $et => $r) {
            /* buscando /categoria. */
            $ca = '/'.$et.'.html';
            if (strpos($url, $ca) !== false) {
                if( $v ) { Mage::log("searchCategdoryAccess ==> $et ==> found", Zend_Log::INFO, 'category_access.log'); }
                return $et;
            }

            /* buscando /categoria. */
            $ca = '/'.$et.'/';
            if (strpos($url, $ca) !== false) {
                if( $v ) { Mage::log("searchCategdoryAccess ==> $et ==> found", Zend_Log::INFO, 'category_access.log'); }
                return $et;
            }
        }

        if( $v ) { Mage::log("searchCategdoryAccess ==> $et ==> not found", Zend_Log::INFO, 'category_access.log'); }
        return '';
    }
    /**
     * add function for rmorales@mlg.com.mx
     *  obtiene los datos del usuario logueado, en caso de que no haya usuario loguado regresa null
     * @return boolean
     */
    public function userCategoryAccess( $url='' ){
        //Mage::log("userCategoryAccess()", Zend_Log::INFO, 'category_access.log');

        $user = Mage::getSingleton('customer/session');
        $user_login = $user->isLoggedIn();

        $a = null;

        if( !$user_login ){ return $a; }

        $user = Mage::getSingleton('customer/session')->getCustomer();

        $a['user_email'] = $user->getEmail();
        $a['user_id'] = $user->getId();

        return $a;
    }
    /**
     * add function for rmorales@mlg.com.mx
     *  busca si el email del usuario esta asinado
     * @return boolean
     */
    public function userCategoryAccessAsign( $u=null, $list=null ){
        //Mage::log("userCategoryAccessAsign()", Zend_Log::INFO, 'category_access.log');

        if( $u==null ){ return false; }
        if( $list==null ){ return false; }

        foreach ($list as $et => $r) {
            if( $u == $r ){
                return true;
            }
        }

        return false;
    }
    /**
     * add function for rmorales@mlg.com.mx
     *  obtiene el nombre de la categoria bloqueada
     * @return string
     */
    public function getCategoryBlocked(){
        return $this->categoryBlocked;
    }
    /* determina si el usuario es un usuario AAA */
    public function is_user_aaa(){
        if( !Mage::getSingleton('customer/session')->isLoggedIn() ){
            return false;
        }

        $group_id = Mage::getSingleton('customer/session')->getCustomerGroupId();
        $group = Mage::getModel('customer/group')->load($group_id);

        $is_user_aaa = false;

        /* Clientes AAA */
        if( $group->getCode() == 'Clientes AAA' ){
            $is_user_aaa = true;
        }

        Mage::getSingleton('core/session')->setUserAAA( $is_user_aaa );
        return $is_user_aaa;
    }
}
