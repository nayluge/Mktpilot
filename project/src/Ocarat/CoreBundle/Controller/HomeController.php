<?php

namespace Ocarat\CoreBundle\Controller;

use Ocarat\CoreBundle\Entity\OrderTracking;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\Query\ResultSetMapping;

class HomeController extends Controller
{

    public function getMonths(){
        for($i=0; $i<24; $i++){
            $months[] = date("m-Y", time()-60*60*24*30*$i);
        }
        return $months;
    }



    private function getOrdersAndValue($campaign, $dateFrom, $dateTo, $medium = null){
        $nbOrders = 0;

        /* on recupere la liste des status a ne pas prendre en compte */

        $em = $this->getDoctrine()->getManager();
        $badStatus = $em->getRepository("OcaratCoreBundle:Status")->findBy(array('usedForAmount' => false));
        $aBadStatus = array();
        foreach($badStatus as $status){
            $aBadStatus[] = $status->getId();
        }
        $aBadStatus[] =0;

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('total', 'total');
        $rsm->addScalarResult('nbOrder', 'nbOrder');
        if($medium == null){
            $query = $this->getDoctrine()->getManager()
                ->createNativeQuery('SELECT ROUND(SUM(orderPrice),2) as total, count(*) as nbOrder FROM OrderTracking WHERE dateOrder BETWEEN ? AND ? And campaign = ? AND orderStatus NOT IN ('.implode(",", $aBadStatus).')', $rsm);

            $query->setParameter(1, $dateFrom);
            $query->setParameter(2, $dateTo);
            $query->setParameter(3, $campaign);

        }
        else{
            $query = $this->getDoctrine()->getManager()
                ->createNativeQuery('SELECT ROUND(SUM(orderPrice),2) as total, count(*) as nbOrder FROM OrderTracking WHERE dateOrder BETWEEN ? AND ? And campaign = ? And medium = ? AND orderStatus NOT IN ('.implode(",", $aBadStatus).')', $rsm);

            $query->setParameter(1, $dateFrom);
            $query->setParameter(2, $dateTo);
            $query->setParameter(3, $campaign);
            $query->setParameter(4, $medium);

        }

        $orders = $query->getResult();

        $ordersValues= 0;


        $nbOrders = $orders[0]["nbOrder"];
        $ordersValues = $orders[0]["total"];

        return array('nbOrders'=> $nbOrders, "ordersValues" => $ordersValues);

    }


    private function getDateAndOrdersAndValue($dateFrom, $dateTo, $campaign=null, $group="month"){
        $ret = array();

        $em = $this->getDoctrine()->getManager();
        $badStatus = $em->getRepository("OcaratCoreBundle:Status")->findBy(array('usedForAmount' => false));
        $aBadStatus = array();
        foreach($badStatus as $status){
            $aBadStatus[] = $status->getId();
        }
        $aBadStatus[] =0;

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('total', 'total');
        $rsm->addScalarResult('nbOrder', 'nbOrder');
        $rsm->addScalarResult('dateOrder', 'dateOrder');


        if($campaign == null){

            $query = $this->getDoctrine()->getManager()
                ->createNativeQuery('SELECT ROUND(SUM(orderPrice),2) as total, count(*) as nbOrder, dateOrder FROM  OrderTracking WHERE orderStatus NOT IN ('.implode(",", $aBadStatus).') AND (dateOrder BETWEEN ? AND ?)  GROUP BY dateOrder', $rsm);

            $query->setParameter(1, $dateFrom);
            $query->setParameter(2, $dateTo);


        }
        else{
            $query = $this->getDoctrine()->getManager()
                ->createNativeQuery('SELECT ROUND(SUM(orderPrice),2) as total, count(*) as nbOrder, dateOrder FROM OrderTracking WHERE (dateOrder BETWEEN ? AND ?) And campaign = ? AND orderStatus NOT IN ('.implode(",", $aBadStatus).') GROUP BY dateOrder', $rsm);

            $query->setParameter(1, $dateFrom);
            $query->setParameter(2, $dateTo);
            $query->setParameter(3, $campaign);

        }





        $dayFirst = new \DateTime($dateFrom);
        $dateTo  = new \DateTime($dateTo);
        $i = new \DateInterval('P1D');
        if($group == "month"){
            $format = "Y-m";
        }
        else{
            $format = "Y-m-d";
        }
        while($dayFirst != $dateTo){
            $retCa[$dayFirst->format($format)] = 0;
            $retNb[$dayFirst->format($format)] = 0;
            $retCost[$dayFirst->format($format)] = 0;
            $dayFirst->add($i);
        }

        $retCa[$dateTo->format($format)] = 0;
        $retNb[$dateTo->format($format)] = 0;
        $retCost[$dateTo->format($format)] = 0;




        $orders = $query->getResult();

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('campaign', 'campaign');
        $rsm->addScalarResult('dateFrom', 'dateFrom');
        $rsm->addScalarResult('cost', 'cost');

        if($campaign == null){
        $query = $this->getDoctrine()->getManager()
            ->createNativeQuery('SELECT campaign, sum(cost) as cost, dateFrom FROM CampaignCost WHERE dateTo  BETWEEN ? AND ? GROUP BY campaign, dateFrom', $rsm);
        $query->setParameter(1, $dateFrom);
        $query->setParameter(2, $dateTo);
        }
        else{
            $query = $this->getDoctrine()->getManager()
                ->createNativeQuery('SELECT campaign, sum(cost) as cost, dateFrom FROM CampaignCost WHERE dateFrom  BETWEEN ? AND ? AND campaign = ? GROUP BY campaign, dateFrom', $rsm);
            $query->setParameter(1, $dateFrom);
            $query->setParameter(2, $dateTo);
            $query->setParameter(3, $campaign);
        }

        $costs = $query->getResult();




        foreach($costs as $cost){
            $d = new \DateTime($cost["dateFrom"]);
            $retCost[$d->format($format)]+= $cost["cost"];

        }



        foreach($orders as $aOrder){
            $d = new \DateTime($aOrder["dateOrder"]);

            $retNb[$d->format($format)] += $aOrder["nbOrder"];
            $retCa[$d->format($format)] += $aOrder["total"];

        }





        return array("nb" => $retNb, "ca" =>$retCa, 'cost' => $retCost);

    }

    /**
     * @Route("/detail", name="detail")
     * @Template()
     */
    public function detailAction(Request $request)
    {


        $monthFrom = $request->get('monthFrom');
        $monthTo = $request->get('monthTo');

        if(is_null($monthFrom) or is_null($monthTo)){
            $monthFrom = $monthTo = date("m")."-".date("Y");

        }

        $t = explode("-", $monthFrom);

        $dateFrom = date("Y-m-d", mktime(0,0,0,$t[0], 1, $t[1]));

        $t = explode("-", $monthTo);
        $dateTo = date("Y-m-d", mktime(23,59,59,$t[0]+1, 0, $t[1]));

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('campaign', 'campaign');
        $rsm->addScalarResult('nb', 'nb');


        $query = $this->getDoctrine()->getManager()
            ->createNativeQuery('SELECT campaign, count(*) as nb FROM OrderTracking WHERE dateOrder BETWEEN ? AND ?  GROUP BY campaign', $rsm);

        $query->setParameter(1, $dateFrom);
        $query->setParameter(2, $dateTo);

        $orders = $query->getResult();





        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('campaign', 'campaign');
        $rsm->addScalarResult('cost', 'cost');

        $query = $this->getDoctrine()->getManager()
            ->createNativeQuery('SELECT campaign, sum(cost) as cost FROM CampaignCost WHERE dateTo  BETWEEN ? AND ? GROUP BY campaign', $rsm);
        $query->setParameter(1, $dateFrom);
        $query->setParameter(2, $dateTo);

        $costs = $query->getResult();




        foreach($costs as $cost){
            $result[$cost["campaign"]]["cost"] = $cost["cost"];
            $result[$cost["campaign"]]["nbOrder"] = 0;
            $result[$cost["campaign"]]["Ca"] = 0;

        }

        $catotal  = 0;

        foreach($orders as $order){
            $val = $this->getOrdersAndValue($order["campaign"], $dateFrom, $dateTo);

            $result[$order["campaign"]]["nbOrder"] = $val["nbOrders"];
            $result[$order["campaign"]]["Ca"] = $val["ordersValues"];
            $catotal+= $val["ordersValues"];
            if(!isset($result[$order["campaign"]]["cost"]))
                $result[$order["campaign"]]["cost"] = 0;
        }


        return array(
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'monthFrom' => $monthFrom,
            'monthTo' => $monthTo,
            'campaigns' => $result,
            'page' => 'detail',
            'showMonth' => true,
            'months' => $this->getMonths(),
            'ca' => $catotal
        );
    }

    /**
     * @Route("/campaign/{campaignName}", name="campaign" , requirements={"campaignName" = ".+"})
     * @Template()
     */
    public function campaignAction(Request $request)
    {

        $campaignName = $request->get('campaignName');

        if($campaignName == "google / cpc"){
            $showMonth = false;
            $group = "day";
        }
        else{
            $showMonth = true;
            $group = "month";
        }



        $dateFrom = $request->get('dateFrom');
        $dateTo = $request->get('dateTo');

        if(is_null($dateFrom) or is_null($dateTo)){
            $monthFrom = $request->get('monthFrom');
            $monthTo = $request->get('monthTo');

            if(is_null($monthFrom) or is_null($monthTo)){
                $monthFrom = $monthTo = date("m")."-".date("Y");

            }

            $t = explode("-", $monthFrom);

            $dateFrom = date("Y-m-d", mktime(0,0,0,$t[0], 1, $t[1]));

            $t = explode("-", $monthTo);
            $dateTo = date("Y-m-d", mktime(23,59,59,$t[0]+1, 0, $t[1]));
        }else{
            $format = strptime($dateFrom, "%Y-%m-%d");
            $monthFrom = date("m-Y", mktime(0,0,0,$format["tm_mon"]+1, 1, $format["tm_year"]+1900));
            $format = strptime($dateTo, "%Y-%m-%d");
            $monthTo = date("m-Y", mktime(0,0,0,$format["tm_mon"]+1, 25, $format["tm_year"]+1900));
        }

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('medium', 'medium');
        $rsm->addScalarResult('nb', 'nb');


        $query = $this->getDoctrine()->getManager()
            ->createNativeQuery('SELECT medium, count(*) as nb FROM OrderTracking WHERE dateOrder BETWEEN ? AND ? AND campaign = ? GROUP BY medium', $rsm);

        $query->setParameter(1, $dateFrom);
        $query->setParameter(2, $dateTo);
        $query->setParameter(3, $campaignName);

        $orders = $query->getResult();

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('medium', 'medium');
        $rsm->addScalarResult('cost', 'cost');

        $query = $this->getDoctrine()->getManager()
            ->createNativeQuery('SELECT medium, sum(cost) as cost FROM CampaignCost WHERE dateTo  BETWEEN ? AND ? AND campaign = ? GROUP BY medium', $rsm);
        $query->setParameter(1, $dateFrom);
        $query->setParameter(2, $dateTo);
        $query->setParameter(3, $campaignName);

        $costs = $query->getResult();

        foreach($costs as $cost){
            $result[$cost["medium"]]["cost"] = $cost["cost"];
            $result[$cost["medium"]]["nbOrder"] = 0;
            $result[$cost["medium"]]["Ca"] = 0;
        }

        $catotal = 0;

        foreach($orders as $order){
            $val = $this->getOrdersAndValue($campaignName, $dateFrom, $dateTo, $order["medium"]);

            $result[$order["medium"]]["nbOrder"] = $val["nbOrders"];
            $result[$order["medium"]]["Ca"] = $val["ordersValues"];
            $catotal += $val["ordersValues"];
            if(!isset($result[$order["medium"]]["cost"]))
                $result[$order["medium"]]["cost"] = 0;
        }



        $graph = $this->getDateAndOrdersAndValue($dateFrom, $dateTo, $campaignName, $group);



        return array(
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'monthFrom' => $monthFrom,
            'monthTo' => $monthTo,
            'mediums' => $result,
            'campaignName' => $campaignName,
            'page' => 'detail',
            'showMonth' => $showMonth,
            'months' => $this->getMonths(),
            'graphCa' => array_values($graph["ca"]),
            'graphNb' => array_values($graph["nb"]),
            'graphCost' => array_values($graph["cost"]),
            'graphDate' => array_keys($graph["nb"]),
            'ca' => $catotal
        );


    }

    /**
     * @Route("/", name="home")
     * @Template()
     */
    public function dashboardAction(Request $request){

        $graph = $this->getDateAndOrdersAndValue(date("Y-m", time()-60*60*24*365)."-01", date("Y-m-d"));

        $statsService = $this->get('ocarat_core.stats');

        $today = new \DateTime();
        $firstDayOfMonth = new \DateTime(date("Y-m-01"));

        $oneYearInterval = new \DateInterval('P1Y');

        $lastYearToday = clone $today;
        $lastYearToday->sub($oneYearInterval);

        $lastYearFirstDayOfMonth = clone $firstDayOfMonth;
        $lastYearFirstDayOfMonth->sub($oneYearInterval);

        $nbOrderDay = $statsService->getNbOrder($today, $today);
        $nbOrderDayLastYear = $statsService->getNbOrder($lastYearToday, $lastYearToday);
        $nbOrderMonth = $statsService->getNbOrder($firstDayOfMonth, $today);
        $nbOrderMonthLastYear = $statsService->getNbOrder($lastYearFirstDayOfMonth, $lastYearToday);

        $valueOrderDay = $statsService->valueOfOrders($today, $today);
        $valueOrderDayLastYear = $statsService->valueOfOrders($lastYearToday, $lastYearToday);
        $valueOrderMonth = $statsService->valueOfOrders($firstDayOfMonth, $today);
        $valueOrderMonthLastYear = $statsService->valueOfOrders($lastYearFirstDayOfMonth, $lastYearToday);

        $cartDay = $statsService->avgCartOfOrders($today, $today);
        $cartDayLastYear = $statsService->avgCartOfOrders($lastYearToday, $lastYearToday);
        $cartMonth = $statsService->avgCartOfOrders($firstDayOfMonth, $today);
        $cartMonthLastYear = $statsService->avgCartOfOrders($lastYearFirstDayOfMonth, $lastYearToday);

        $visitDay = $statsService->getNbVisits($today, $today);
        $visitDayLastYear = $statsService->getNbVisits($lastYearToday, $lastYearToday);
        $visitMonth = $statsService->getNbVisits($firstDayOfMonth, $today);
        $visitMonthLastYear = $statsService->getNbVisits($lastYearFirstDayOfMonth, $lastYearToday);

        //var_dump($nbOrder); exit;
        return array(
            "page" => "dashboard",
            "nbOrderDay" => $nbOrderDay,
            "nbOrderDayLastYear" => $nbOrderDayLastYear,
            "nbOrderMonth" => $nbOrderMonth,
            "nbOrderMonthLastYear" => $nbOrderMonthLastYear,
            "valueOrderDay" => $valueOrderDay,
            "valueOrderDayLastYear" => $valueOrderDayLastYear,
            "valueOrderMonth" => $valueOrderMonth,
            "valueOrderMonthLastYear" => $valueOrderMonthLastYear,
            "cartDay" => $cartDay,
            "cartDayLastYear" => $cartDayLastYear,
            "cartMonth" => $cartMonth,
            "cartMonthLastYear" => $cartMonthLastYear,
            "visitDay" => $visitDay,
            "visitDayLastYear" => $visitDayLastYear,
            "visitMonth" => $visitMonth,
            "visitMonthLastYear" => $visitMonthLastYear,
            'graphCa' => array_values($graph["ca"]),
            'graphNb' => array_values($graph["nb"]),
            'graphCost' => array_values($graph["cost"]),
            'graphDate' => array_keys($graph["nb"]),
        );
    }

}
