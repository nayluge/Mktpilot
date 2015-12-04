<?php

namespace Ocarat\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ocarat\CoreBundle\Entity\OrderTracking;

class ChooseTrackingCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('orders:tracking')
            ->setDescription('choix du tracking entre GA ou interne');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $orderTrackingRepo = $em->getRepository('OcaratCoreBundle:OrderTracking');

        $orders = $orderTrackingRepo->getTrackableOrder();

        var_dump(count($orders));

        $orderManager = $this->getContainer()->get('ocarat_core.order');

        foreach($orders as $order){
            $orderManager->determineTracking($order);
        }
    }
}