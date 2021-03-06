<?php

namespace Oro\Bridge\CustomerAccount\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\CustomerBundle\Entity\Customer;

class CustomerCreateListener
{
    const COMMERCE_CHANNEL_TYPE = 'commerce';

    /**
     * @param Customer $customer
     * @param LifecycleEventArgs $args
     */
    public function postPersist(Customer $customer, LifecycleEventArgs $args)
    {
        $this->updateDataChannel($customer, $args);
    }

    /**
     * @param Customer $customer
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(Customer $customer, LifecycleEventArgs $args)
    {
        $this->updateDataChannel($customer, $args);
    }

    /**
     * @param Customer $customer
     * @param LifecycleEventArgs $args
     */
    private function updateDataChannel(Customer $customer, LifecycleEventArgs $args)
    {
        $em = $args->getEntityManager();

        if (!$customer->getDataChannel()) {
            $channels = $em->getRepository('OroChannelBundle:Channel')
                ->findBy(['channelType' => self::COMMERCE_CHANNEL_TYPE]);
            if (count($channels) === 1) {
                $customer->setDataChannel(reset($channels));
            }
        }
    }
}
