<?php

class Entrepids_Newsletter_Model_Queue extends Mage_Newsletter_Model_Queue
{
    
    public function sendPerSubscriber($count=20, array $additionalVariables=array())
    {  
        
        if($this->getQueueStatus()!=self::STATUS_SENDING
           && ($this->getQueueStatus()!=self::STATUS_NEVER && $this->getQueueStartAt())
        ) {
            return $this;
        }
        $collection = Mage::getModel("entrepids_newsletter/subscriptions")
                ->getCollection()
                ->addFieldToFilter("catalog_id",$this->getCatalogId())
                ->load();
        
        if ($collection->getSize() == 0) {
            $this->_finishQueue();
            return $this;
        }
        
        
        /** @var Mage_Newsletter_Model_Resource_Subscriber_Collection $collection */
         
        /** @var Mage_Core_Model_Email_Template $sender */
        $sender = Mage::getModel('core/email_template');
        $sender->setSenderName($this->getNewsletterSenderName())
            ->setSenderEmail($this->getNewsletterSenderEmail())
            ->setTemplateType(self::TYPE_HTML)
            ->setTemplateSubject($this->getNewsletterSubject())
            ->setTemplateText($this->getNewsletterText())
            ->setTemplateStyles($this->getNewsletterStyles())
            ->setTemplateFilter(Mage::helper('newsletter')->getTemplateProcessor());
        
        $customerIds = [];
        foreach($collection as $register){
            $customerIds[] = $register->getCustomerId();
        }
        $customers = Mage::getModel("customer/customer")
                ->getCollection()
                ->addFieldToFilter('entity_id', array('in'=>$customerIds))
                ->addAttributeToSelect('email')
                ->addAttributeToSelect('full_name')
                ->load();
        foreach($customers as $customer) {
            $email = $customer->getEmail();
            $name = $customer->getFullName();

            $sender->emulateDesign($customer->getStoreId());
            $successSend = $sender->send($email, $name, array('subscriber' => $customer));
            $sender->revertDesign();
            
            if($successSend) {
               // $model->received($this);
            } else {
                
                $problem = Mage::getModel('newsletter/problem');
                $notification = Mage::helper('newsletter')->__('Please refer to exeption.log');
                $problem->addQueueData($this)
                    ->addErrorData(new Exception($notification))
                    ->save();
             //   $model->received($this);
            }
        }
        if(count($collection->getItems()) < $count-1 || count($collection->getItems()) == 0) {
            $this->_finishQueue();
        }
        return $this;
    }

}
