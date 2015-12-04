<?php

namespace Ocarat\CoreBundle\Service;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Ocarat\CoreBundle\Entity\OrderTracking;
use Symfony\Component\DependencyInjection\ContainerAware;

class Order{

    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(ManagerRegistry $doctrine)
    {

        $this->em = $doctrine->getManager('default');
        $this->emoc = $doctrine->getManager('site');
    }

    public function determineTracking(OrderTracking $order)
    {

        $analitycsCampaign = $campaign = $order->getCampaign();
        $analitycsMedium = $medium = $order->getMedium();
        $orderHistory = $order->getCampaignHistory();
        if(count($orderHistory) > 0) {
            foreach($orderHistory as $history) {
                if($history[0] != 'ws.colissimo.fr / referral') {
                    $campaign = $history[0];
                    $medium = $history[1];
                }
            }
        }

        $order->setCampaign($campaign);
        $order->setMedium($medium);
        $order->setAnalitycsCampaign($analitycsCampaign);
        $order->setAnalyticsMedium($analitycsMedium);
        $this->em->persist($order);
        $this->em->flush();


    }

    public function updateInfo($orderId)
    {

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('orderValue', 'orderValue');

        $rsm2 = new ResultSetMapping();
        $rsm2->addScalarResult('id_order_state', 'id_order_state');


        /* on recupere le prix */
        $query = $this->emoc->createNativeQuery(
            'SELECT (total_products + total_shipping - total_discounts/1.2) as orderValue FROM ps_orders WHERE id_order = ?', $rsm);

        $query->setParameter(1, $orderId);


        $orderValue = $query->getResult();


        $orderValue = $orderValue[0]["orderValue"];
        /* on recupere le dernier status */
        $query = $this->emoc->createNativeQuery(
            'SELECT id_order_state FROM ps_order_history WHERE id_order = ? ORDER BY date_add DESC', $rsm2);
        $query->setParameter(1, $orderId);

        $orderState = $query->getResult();
        $orderState = intval($orderState[0]["id_order_state"]);

        /* on recupere l'historique */
        $q = 'SELECT campaigns FROM ps_oc_pilot WHERE id_order = '.$orderId;
        $res = $this->emoc->getConnection('site')->query($q)->fetch();

        if(count($res)){
            $campaigns = json_decode($res["campaigns"]);
        }
        else
            $campaigns = array();



        $o = $this->em->getRepository("OcaratCoreBundle:OrderTracking")->findOneBy(array('orderId'=> $orderId));
        if(is_null($o)) return;
        else{
            $o->setOrderPrice($orderValue);
            $o->setOrderStatus($orderState);
            $o->setCampaignHistory($campaigns);
            $this->em->persist($o);
            $this->em->flush();
        }
    }

}