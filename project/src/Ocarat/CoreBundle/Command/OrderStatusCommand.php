<?php

namespace Ocarat\CoreBundle\Command;

use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ocarat\CoreBundle\Entity\OrderTracking;

class OrderStatusCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('orders:getStatus')
            ->setDescription('recuperation des status de commande');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $em = $this->getContainer()->get('doctrine')->getManager();

        $orders = $em->getRepository("OcaratCoreBundle:OrderTracking")->findBy(array('orderStatus' => 0), array('dateOrder' => 'desc'), 500);

        if(count($orders))
        {
            $orderService = $this->getContainer()->get('ocarat_core.order');
            foreach($orders as $order){
		var_dump($order->getOrderId());
                $orderService->updateInfo($order->getOrderId());
            }
        }



        $emoc = $this->getContainer()->get('doctrine')->getManager('site');

        $dateInteval = new \DateInterval("P3D");
        $date = new \DateTime();
        $date->sub($dateInteval);

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id_order', 'id_order');
        $rsm->addScalarResult('date_add', 'date_add');
        $query = $this->getContainer()->get('doctrine')->getManager('site')
            ->createNativeQuery('SELECT DISTINCT id_order, date_add FROM ps_order_history WHERE date_add > ?', $rsm);

        $query->setParameter(1, date("Y-m-d", time()-60*60*24*1));
        $orders = $query->getResult();


        foreach($orders as $order)
        {
            $orderService = $this->getContainer()->get('ocarat_core.order');
            $orderService->updateInfo($order['id_order']);
        }


    }
}
