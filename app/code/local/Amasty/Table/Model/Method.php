<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Table
 */ 
class Amasty_Table_Model_Method extends Mage_Core_Model_Abstract
{
    const UPLOAD_DIR = 'amtable';
    const IMG_TAG = '{IMG}';

    /** @var Mage_Core_Model_Resource_Db_Collection_Abstract */
    protected $_storeDataCollection;
    protected $_storeEntity;
    protected $_storeJoinField = 'method_id';

    public function _construct()
    {
        parent::_construct();
        $this->_init('amtable/method');
        $this->_storeEntity = $this->getResourceName() . '_store';
    }
    
    public function massChangeStatus ($ids, $status) {
        foreach ($ids as $id) {
                $model = Mage::getModel('amtable/method')->load($id);
                $model->setIsActive($status);
                $model->save();
            }
        return $this;
    }

    public function addComment($html, $storeId)
    {
        preg_match_all('/<label\\s*for="s_method_amtable_amtable(.+?)">.*\\s+.*/i', $html, $matches);
        if (!empty($matches[0])) {
            $hashMethods = Mage::getModel('amtable/method')->getCollection()->hashComment($storeId);
            foreach ($matches[0] as $key => $value) {
                $methodId = $matches[1][$key];
                $hashMethods[$methodId] = str_replace(
                    self::IMG_TAG,
                    '</div><img src="' . $this->getImageUrl($methodId) . '" /><div>',
                    $hashMethods[$methodId]
                );
                $to[] = $matches[0][$key] . '<div>' . $hashMethods[$methodId] . '</div>';
            }

            $newHtml = str_replace($matches[0], $to, $html);
            return $newHtml;
        }

        return $html;
    }

    /**
     * @param $methodId
     * @return string
     */
    public function getImageUrl($methodId)
    {
        return $this->load($methodId)->getCommentImg() ?
            Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . $this->load($methodId)->getCommentImg() :
            '';
    }

    public function getFreeTypes()
    {
        $result = array();
        $freeTypesString = trim($this->getData('free_types'),',');
        if ($freeTypesString) {
            $result = explode(',', $freeTypesString);
        }
        return $result;
    }

    public function getLabel($storeId = 0, $useDefault = true)
    {
        if ($storeId == 0) {
            return $this->getName();
        }
        /** @var Amasty_Customform_Model_Form_Store $storeData */
        $storeData = $this->getStoreData($storeId);
        $result = is_object($storeData) ? $storeData->getLabel() : null;
        if (!$result && $storeId && $useDefault) {
            return $this->getLabel(0);
        }
        return $result;
    }

    public function getCommentLabel($storeId = 0, $useDefault = true)
    {
        if ($storeId == 0) {
            return $this->getComment();
        }
        /** @var Amasty_Customform_Model_Form_Store $storeData */
        $storeData = $this->getStoreData($storeId);
        $result = is_object($storeData) ? $storeData->getComment() : null;
        if (!$result && $storeId && $useDefault) {
            return $this->getCommentLabel(0);
        }
        return $result;
    }

    public function setLabels(array $labels)
    {
        foreach ($labels as $storeId => $label) {
            /** @var Amasty_Table_Model_Method_Store $storeData */
            $storeData = $this->getStoreData($storeId);
            if (is_null($storeData)) {
                $storeData = $this->createStoreData($storeId);
            }

            $storeData->setLabel($label);
        }

        return $this;
    }

    public function setComments(array $comments)
    {
        foreach ($comments as $storeId => $comment) {
            /** @var Amasty_Table_Model_Method_Store $storeData */
            $storeData = $this->getStoreData($storeId);
            if (is_null($storeData)) {
                $storeData = $this->createStoreData($storeId);
            }

            $storeData->setComment($comment);
        }

        return $this;
    }

    protected function getStoreData($storeId)
    {
        $storeData = $this->getId()
            ? $this->getStoreDataCollection()->getItemByColumnValue('store_id', $storeId)
            : null;
        return $storeData;
    }

    protected function getStoreDataCollection()
    {
        if (is_null($this->_storeDataCollection)) {
            $this->_storeDataCollection = Mage::getModel($this->_storeEntity)->getCollection();

            if ($this->getId()) {
                $this->_storeDataCollection->addFilter($this->_storeJoinField, $this->getId());
                $this->_storeDataCollection->load();
            }
        }

        return $this->_storeDataCollection;
    }

    protected function createStoreData($storeId)
    {
        $storeData = Mage::getModel($this->_storeEntity);
        $storeData->setData($this->_storeJoinField, $this->getId());
        $storeData->setData('store_id', $storeId);

        $this->getStoreDataCollection()->addItem($storeData);

        return $storeData;
    }

    protected function _afterSave()
    {
        if (!$this->isDeleted()) {
            if ($this->hasData('label_') || $this->hasData('comment_')) {
                $this->setLabels($this->getData('label_'));
            }
            if ($this->hasData('comment_')) {
                $this->setComments($this->getData('comment_'));
            }
            
            parent::_afterSave();
            
            if ($this->_storeDataCollection) {
                $this->_storeDataCollection->save();
            }
            
            return $this;
        }

        return parent::_afterSave();
    }

    /**
     * @param $id
     * @param $data
     * @return string
     * @throws Exception
     */
    public function saveImage($id, $data)
    {
        $result = '';
        if (isset($_FILES['comment_img']['name']) and (file_exists($_FILES['comment_img']['tmp_name']))) {
            try {
                $uploader = new Varien_File_Uploader('comment_img');
                $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png', 'svg'));
                $uploader->setAllowRenameFiles(false);
                $uploader->setFilesDispersion(false);

                $path = Mage::getBaseDir('media') . DS . self::UPLOAD_DIR . DS . $id;

                $uploadResult = $uploader->save($path, $_FILES['comment_img']['name']);

                $result = $uploadResult ? self::UPLOAD_DIR . DS . $id . DS . $uploadResult['file'] : '';
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        } else {
            if (!isset($data['comment_img']['delete']) || !$data['comment_img']['delete']) {
                $result = $this->load($id)->getCommentImg();
            }
        }

        return $result;
    }
}
