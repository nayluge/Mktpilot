<?php

namespace Ocarat\CoreBundle\Controller;

use Doctrine\ORM\Query\ResultSetMapping;
use Ocarat\CoreBundle\Entity\CampaignCost;
use Ocarat\CoreBundle\Entity\Status;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class CostController extends Controller
{
    /**
     * @Route("/cost/", name="costs")
     * @Template()
     */
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $month = $request->get('month');
        $year = $request->get('year');

        if(is_null($month) or is_null($year)){
            $month = date("m");
            $year = date("Y");
        }

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('campaign', 'campaign');
        $rsm->addScalarResult('month', 'month');
        $rsm->addScalarResult('year', 'year');
        $rsm->addScalarResult('nbOrder', 'nbOrder');

        $query = $em->createNativeQuery('
        SELECT campaign, month(dateOrder) as month, year(dateOrder) as year, count(*) as nbOrder FROM OrderTracking WHERE campaign NOT LIKE \'google%cpc%\' AND MONTH(dateOrder) = ? AND YEAR(dateOrder) = ? GROUP BY campaign, YEAR(dateOrder), MONTH(dateOrder)', $rsm);

        $query->setParameter(1, $month);
        $query->setParameter(2, $year);

        $campaigns = $query->getResult();


        $costRepo = $em->getRepository("OcaratCoreBundle:CampaignCost");

        foreach($campaigns as $campaign){
            $oDateFrom = new \DateTime();
            $oDateFrom->setTimestamp( mktime(0,0,0,$campaign["month"], 1, $campaign["year"]));

            $oDateTo= new \DateTime();
            $oDateTo->setTimestamp( mktime(0,0,0,$campaign["month"]+1, 0, $campaign["year"]));

            $cost = $costRepo->findOneBy(
                array(
                    "dateFrom" => $oDateFrom,
                    "dateTo" => $oDateTo,
                    "campaign" => $campaign["campaign"]
                )
            );

            if(is_null($cost)){
                $cost = new CampaignCost();
                $cost->setCost(0);
                $cost->setMedium('');
                $cost->setDateFrom($oDateFrom);
                $cost->setDateTo($oDateTo);
                $cost->setCampaign($campaign["campaign"]);
                $em->persist($cost);
            }
        }
        $em->flush();

        $costs = $costRepo->findBy(
            array(
                "dateFrom" => $oDateFrom,
                "dateTo" => $oDateTo,
            )
        );

        for($i=2013; $i<= date("Y"); $i++)
            $years[] = $i;

        return array('costs' => $costs, 'page' => 'costs', 'month' => $month, 'year' => $year, 'years' =>$years);



    }

    /**
     * @Route("/cost/update/{cost}", name="cost_update")
     */
    public function updateAction(Request $request, CampaignCost $cost)
    {
        $val = $request->get('value');
        $val = str_replace(",",".", $val);
        if(is_numeric($val)){
            $cost->setCost($val);
            $em = $this->getDoctrine()->getManager();
            $em->persist($cost);
            $em->flush();
            echo "1"; exit;
        }
        else{
            echo "0"; exit;
        }


    }

}
