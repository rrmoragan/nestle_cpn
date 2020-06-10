<?php
/**
 * Category tabs
 *
 * @category   Entrepids
 * @package    Entrepids_CoffeeSolutions
 * @author     miguel.perez@entrepids.com
 * @website    http://www.entrepids.com
 */
class Entrepids_CoffeeSolutions_Block_Adminhtml_Catalog_Category_Tabs  extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Default Attribute Tab Block
     *
     * @var string
     */
    protected $_attributeTabBlock = 'adminhtml/catalog_category_tab_attributes';

    /**
     * Initialize Tabs
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('category_info_tabs');
        $this->setDestElementId('category_tab_content');
        $this->setTitle(Mage::helper('catalog')->__('Category Data'));
        $this->setTemplate('widget/tabshoriz.phtml');
    }

    /**
     * Retrieve cattegory object
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCategory()
    {
        return Mage::registry('current_category');
    }

    /**
     * Return Adminhtml Catalog Helper
     *
     * @return Mage_Adminhtml_Helper_Catalog
     */
    public function getCatalogHelper()
    {
        return Mage::helper('adminhtml/catalog');
    }

    /**
     * Getting attribute block name for tabs
     *
     * @return string
     */
    public function getAttributeTabBlock()
    {
        if ($block = $this->getCatalogHelper()->getCategoryAttributeTabBlock()) {
            return $block;
        }
        return $this->_attributeTabBlock;
    }
    
    /**
     * @params $categoryIdsArray int[] Array of Ids to find childs
     */
    protected function getCategoryChildsIds($categoryIdsArray){
        $ids = array();
        if(!empty($categoryIdsArray) && is_array($categoryIdsArray)){
            foreach ($categoryIdsArray as $cid){
                $category = Mage::getModel('catalog/category')->load($cid);
                $childsIds = $category->getChildren();
                $newsIds = explode(',', $childsIds);
                $ids = array_merge($ids,$newsIds);
            }
        }
        return $ids;
    }
    
    protected function getCategoryChildsIdsWithInactive($categoryIdsArray){
        $ids = array();
        if(!empty($categoryIdsArray) && is_array($categoryIdsArray)){
            foreach ($categoryIdsArray as $cid){
                $debug = print_r($cid,true);
                //Mage::log('Cid: '.$debug,null,'cs_category.log');
                $childs = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('entity_id')->addFieldToFilter('parent_id',array('in'=>$cid));
                foreach ($childs as $_c){
                    $ids[] = $_c->getId();
                }
            }
        }
        return $ids;

    }

    /**
     * Prepare Layout Content
     *
     * @return Mage_Adminhtml_Block_Catalog_Category_Tabs
     */
    protected function _prepareLayout()
    {
        $coffeeSolutionCategoryId = Mage::getStoreConfig(Entrepids_CoffeeSolutions_Helper_Data::COFFEE_SOLUTIONS_PATH, Mage::app()->getStore());
        
        $allIdsCSAvialable = array();
        
        if($coffeeSolutionCategoryId){
            $firstLevel = $this->getCategoryChildsIdsWithInactive(array($coffeeSolutionCategoryId));
            $secondLevel = !empty($firstLevel) ? $this->getCategoryChildsIdsWithInactive(array($firstLevel)) : array();
            $allIdsCSAvialable = array_merge($firstLevel,$secondLevel);
            $allIdsProductsNotAvailable = array_merge(array($coffeeSolutionCategoryId),$allIdsCSAvialable);
        }
        
        $categoryAttributes = $this->getCategory()->getAttributes();
        if (!$this->getCategory()->getId()) {
            foreach ($categoryAttributes as $attribute) {
                $default = $attribute->getDefaultValue();
                if ($default != '') {
                    $this->getCategory()->setData($attribute->getAttributeCode(), $default);
                }
            }
        }

        $attributeSetId     = $this->getCategory()->getDefaultAttributeSetId();
        /** @var $groupCollection Mage_Eav_Model_Resource_Entity_Attribute_Group_Collection */
        $groupCollection    = Mage::getResourceModel('eav/entity_attribute_group_collection')
            ->setAttributeSetFilter($attributeSetId)
            ->setSortOrder()
            ->load();
        $defaultGroupId = 0;
        foreach ($groupCollection as $group) {
            /* @var $group Mage_Eav_Model_Entity_Attribute_Group */
            if ($defaultGroupId == 0 or $group->getIsDefault()) {
                $defaultGroupId = $group->getId();
            }
        }

        foreach ($groupCollection as $group) {
            /* @var $group Mage_Eav_Model_Entity_Attribute_Group */
            $attributes = array();
            foreach ($categoryAttributes as $attribute) {
                /* @var $attribute Mage_Eav_Model_Entity_Attribute */
                if ($attribute->isInGroup($attributeSetId, $group->getId())) {
                    $attributes[] = $attribute;
                }
            }

            // do not add grops without attributes
            if (!$attributes ) {
                continue;
            }
            
            //echo 'Current Category ID: '.$this->getCategory()->getId();
            
            if( $group->getAttributeGroupName()=='Solución de Café'&& 
                    (!$this->getCategory()->getId() 
                        || !$coffeeSolutionCategoryId 
                        || !in_array($this->getCategory()->getId(),$allIdsCSAvialable))
                    ){
                continue;
            }
            

            $active  = $defaultGroupId == $group->getId();
            $block = $this->getLayout()->createBlock($this->getAttributeTabBlock(), '')
                ->setGroup($group)
                ->setAttributes($attributes)
                ->setAddHiddenFields($active)
                ->toHtml();
            $this->addTab('group_' . $group->getId(), array(
                'label'     => Mage::helper('catalog')->__($group->getAttributeGroupName()),
                'content'   => $block,
                'active'    => $active
            ));
        }

        if(!$this->getCategory()->getId() || ($this->getCategory()->getId() && !in_array($this->getCategory()->getId(), $allIdsProductsNotAvailable)) ){
            $this->addTab('products', array(
                'label'     => Mage::helper('catalog')->__('Category Products'),
                'content'   => $this->getLayout()->createBlock(
                    'adminhtml/catalog_category_tab_product',
                    'category.product.grid'
                )->toHtml(),
            ));
        }
        
        
        if($this->getCategory()->getId() && in_array($this->getCategory()->getId(), $secondLevel) ){
            /*$this->addTab('solutions', array(
                'label'     => Mage::helper('coffeesolutions')->__('Soluciones disponibles'),
                'content'   => $this->getLayout()->createBlock(
                    'coffeesolutions/adminhtml_catalog_category_tab_soluciones',
                    'edit.coffeesolutions.soluciones'
                )->toHtml(),
            ));*/
            $this->addTab('solutions', array(
                'label' => Mage::helper('coffeesolutions')->__('Soluciones disponibles'),
                'title' => Mage::helper('coffeesolutions')->__('Soluciones disponibles'),
                'url'   => $this->getUrl('coffeesolutions/adminhtml_solutions/solutions', array('_current' => true)),
                'class' => 'ajax',
            ));
        }
        

        // dispatch event add custom tabs
        Mage::dispatchEvent('adminhtml_catalog_category_tabs', array(
            'tabs'  => $this
        ));

        
        return parent::_prepareLayout();
    }
}
