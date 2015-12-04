<?php

namespace Ocarat\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class OrderTrackingRepository extends EntityRepository{

    /**
     * Get the number of order Between 2 date
     *
     * @return integer
     */
    public function countOrders(\DateTime $dateStart, \DateTime $dateEnd)
    {

        $query = $this->_em->createQuery(
            'SELECT count(o)
            FROM OcaratCoreBundle:OrderTracking o
            JOIN OcaratCoreBundle:Status s WITH o.orderStatus = s.statusId
            WHERE o.dateOrder >= :dateStart
            AND o.dateOrder <= :dateEnd
            AND s.usedForAmount = 1'
        )
            ->setParameter('dateStart', $dateStart->format('Y-m-d'))
            ->setParameter('dateEnd', $dateEnd->format('Y-m-d'));

        return $query->getSingleScalarResult();
    }

    /**
     * Get the value of order Between 2 date
     *
     * @return integer
     */
    public function valueOfOrders(\DateTime $dateStart, \DateTime $dateEnd)
    {
        $query = $this->_em->createQuery(
            'SELECT sum(o.orderPrice)
            FROM OcaratCoreBundle:OrderTracking o
            JOIN OcaratCoreBundle:Status s WITH o.orderStatus = s.statusId
            WHERE o.dateOrder >= :dateStart
            AND o.dateOrder <= :dateEnd
            AND s.usedForAmount = 1'
        )
            ->setParameter('dateStart', $dateStart->format('Y-m-d'))
            ->setParameter('dateEnd', $dateEnd->format('Y-m-d'));

        return $query->getSingleScalarResult();
    }

    /**
     * Get the value of order Between 2 date
     *
     * @return integer
     */
    public function avgCartOfOrders(\DateTime $dateStart, \DateTime $dateEnd)
    {
        $query = $this->_em->createQuery(
            'SELECT AVG(o.orderPrice)
            FROM OcaratCoreBundle:OrderTracking o
            JOIN OcaratCoreBundle:Status s WITH o.orderStatus = s.statusId
            WHERE o.dateOrder >= :dateStart
            AND o.dateOrder <= :dateEnd
            AND s.usedForAmount = 1'
        )
            ->setParameter('dateStart', $dateStart->format('Y-m-d'))
            ->setParameter('dateEnd', $dateEnd->format('Y-m-d'));

        return $query->getSingleScalarResult();
    }

    /**
     * Get the order to determine tracking
     *
     * @return integer
     */
    public function getTrackableOrder()
    {
        $oDate = new \DateTime();
        $query = $this->_em->createQuery(
            'SELECT o
            FROM OcaratCoreBundle:OrderTracking o
            WHERE
            ( o.dateOrder != :currdate
            OR
              o.campaignHistory != \'\'
            )
            AND o.analyticsMedium IS NULL'
        )
            ->setParameter('currdate', $oDate->format('Y-m-d'));


        return $query->setMaxResults(200)->getResult();
    }

}
