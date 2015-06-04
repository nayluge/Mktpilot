<?php

namespace Ocarat\CoreBundle\Service;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\DependencyInjection\ContainerAware;

class Stats{

    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(ManagerRegistry $doctrine)
    {

        $this->em = $doctrine->getManager('default');
    }

    public function getNbOrder(\DateTime $dateFrom, \DateTime $dateTo){
        $orderRepo = $this->em->getRepository('OcaratCoreBundle:OrderTracking');

        return $orderRepo->countOrders($dateFrom, $dateTo);
    }


    public function valueOfOrders(\DateTime $dateFrom, \DateTime $dateTo){
        $orderRepo = $this->em->getRepository('OcaratCoreBundle:OrderTracking');
        $value=$orderRepo->valueOfOrders($dateFrom, $dateTo);
        if(!$value) $value =0;
        return $value;
    }

    public function avgCartOfOrders(\DateTime $dateFrom, \DateTime $dateTo){
        $orderRepo = $this->em->getRepository('OcaratCoreBundle:OrderTracking');
        $value=$orderRepo->avgCartOfOrders($dateFrom, $dateTo);
        if(!$value) $value =0;
        return $value;
    }

    public function getNbVisits(\DateTime $dateFrom, \DateTime $dateTo){
        $visitRepo = $this->em->getRepository('OcaratCoreBundle:Visit');
        $value=$visitRepo->countVisits($dateFrom, $dateTo);
        if(!$value) $value =0;
        return $value;
    }

}