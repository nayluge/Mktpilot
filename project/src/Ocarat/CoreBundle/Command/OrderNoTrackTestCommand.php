<?php

namespace Ocarat\CoreBundle\Command;

use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ocarat\CoreBundle\Entity\OrderTracking;

class OrderNoTrackTestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('orders:notracktest')
            ->setDescription('recuperation des commandes non trackées');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $i = $j = 0;
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id_order', 'id_order');
        $rsm->addScalarResult('date_add', 'date_add');
        $query = $this->getContainer()->get('doctrine')->getManager('site')
                ->createNativeQuery('SELECT id_order, date_add FROM ps_orders WHERE date_add > ? AND date_add < ?', $rsm);

        $query->setParameter(1, date("Y-m-d", time()-60*60*24*(90)));
        $query->setParameter(2, date("Y-m-d", time()-60*60*24*0));
        $orders = $query->getResult();

        $em = $this->getContainer()->get('doctrine')->getManager();
        $rep = $em->getRepository("OcaratCoreBundle:OrderTracking");

        foreach($orders as $order){
	    var_dump($order);
            $o = $rep->findOneBy(array('orderId' => $order["id_order"]));
            if(is_null($o)){
		var_dump('pas créé');
                $o = new OrderTracking();
                $date = new \DateTime($order["date_add"]);
                $o->setCampaign("No tracking ?");
                $o->setMedium("");
                $o->setDateOrder($date);
                $o->setOrderId($order["id_order"]);
                $o->setOrderStatus(0);
                $o->setOrderPrice(0);
                $em->persist($o);
            }
            else{
                $j++;
            }
        }

        $em->flush();

    }
}
