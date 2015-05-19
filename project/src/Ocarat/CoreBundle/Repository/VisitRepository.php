<?php

namespace Ocarat\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class VisitRepository extends EntityRepository{

    /**
     * Get the number of order Between 2 date
     *
     * @return integer
     */
    public function countVisits(\DateTime $dateStart, \DateTime $dateEnd)
    {

        $query = $this->_em->createQuery(
            'SELECT sum(o.nbVisit)
            FROM OcaratCoreBundle:Visit o
            WHERE o.dateVisit >= :dateStart
            AND o.dateVisit <= :dateEnd'
        )
            ->setParameter('dateStart', $dateStart->format('Y-m-d'))
            ->setParameter('dateEnd', $dateEnd->format('Y-m-d'));

        return $query->getSingleScalarResult();
    }
}
