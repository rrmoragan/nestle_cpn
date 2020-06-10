<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


class Amasty_Base_Model_Resource_Inbox_Collection extends Mage_AdminNotification_Model_Resource_Inbox_Collection
{
    /**
     * @param \SimpleXMLElement $item
     * @return bool
     */
    public function execute(\SimpleXMLElement $item)
    {
        $this->addFieldToFilter('title', $this->convertString($item->title));

        $url = (string)$item->link;
        if ($url) {
            $this->addFieldToFilter('url', $url);
        }

        return $this->getSize() > 0;
    }

    /**
     * @param \SimpleXMLElement $data
     * @return string
     */
    protected function convertString(\SimpleXMLElement $data)
    {
        $data = htmlspecialchars((string)$data);
        return $data;
    }
}
