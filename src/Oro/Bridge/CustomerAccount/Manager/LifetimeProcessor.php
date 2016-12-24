<?php

namespace Oro\Bridge\CustomerAccount\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;

use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\CustomerBundle\Entity\Account as Customer;
use Oro\Bundle\PaymentBundle\Provider\PaymentStatusProvider;

class LifetimeProcessor
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var CurrencyQueryBuilderTransformerInterface
     */
    protected $qbTransformer;

    /**
     * @param ManagerRegistry $registry
     * @param CurrencyQueryBuilderTransformerInterface $qbTransformer
     */
    public function __construct(ManagerRegistry $registry, CurrencyQueryBuilderTransformerInterface $qbTransformer)
    {
        $this->registry = $registry;
        $this->qbTransformer = $qbTransformer;
    }

    /**
     * @param Customer $customer
     *
     * @return float
     */
    public function calculateLifetimeValue(Customer $customer)
    {
        $qb = $this->getEntityManager()->getRepository('OroOrderBundle:Order')
            ->createQueryBuilder('o');
        $subtotalValueQuery = $this->qbTransformer->getTransformSelectQuery('subtotal', $qb);
        $qb->select(sprintf('SUM(%s)', $subtotalValueQuery))
            ->leftJoin(
                'Oro\Bundle\PaymentBundle\Entity\PaymentStatus',
                'payment_status',
                Join::WITH,
                'payment_status.entityIdentifier = o.id '
                . "AND payment_status.entityClass = 'Oro\\Bundle\\OrderBundle\\Entity\\Order'"
            )
            ->where(
                $qb->expr()->eq('o.account', ':account')
            )
            ->andWhere('payment_status.paymentStatus = :paymentStatus')
            ->setParameter('account', $customer->getId())
            ->setParameter('paymentStatus', PaymentStatusProvider::FULL);

        return (float)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getEntityManager()
    {
        return $this->registry->getManager();
    }
}
