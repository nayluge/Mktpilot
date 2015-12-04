<?php

namespace Ocarat\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ocarat\CoreBundle\Entity\OrderTracking;
use Symfony\Component\Validator\Constraints\DateTime;

class OrderCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ga:getOrders')
            ->setDescription('recuperation des commandes GA');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $client = new \Google_Client();
        $client->setApplicationName('Mkt Pilot');
        $client->setDeveloperKey('AIzaSyAHcDIVIzaT0zGX0MXW1xM3MF5AM5QZpmo');

        $kernel = $this->getContainer()->get('kernel');
        $path = $kernel->locateResource('@OcaratCoreBundle/Resources/crt/Mkt pilot-09a1781d2476.p12');
        $key = file_get_contents($path);
        $cred = new \Google_Auth_AssertionCredentials(
            '788022848300-7om598lks46jla3kbmbcoroajg4t40sn@developer.gserviceaccount.com',
            array('https://www.googleapis.com/auth/analytics.readonly'),
            $key
        );
        $client->setAssertionCredentials($cred);

        $service = new \Google_Service_Analytics($client);
        $projectId = 31618501;

        $em = $this->getContainer()->get('doctrine')->getManager();

        $rep = $em->getRepository("OcaratCoreBundle:OrderTracking");

        $last = $rep->findOneBy(array(), array("dateOrder" => "DESC"));
        $today = new \DateTime();

        $nextDate = $last->getDateOrder();

        for ($j = 0; $j < 365; $j++) {

            $from = date('Y-m-d', $nextDate->getTimestamp()); // 2 days
            $to = date('Y-m-d', $nextDate->getTimestamp()); // today
            $metrics = 'ga:totalValue';
            $dimensions = 'ga:transactionId,ga:source,ga:medium,ga:campaign,ga:year,ga:month,ga:day';
            /*$metrics = 'ga:adCost';
            $dimensions = 'ga:campaign,ga:date';*/
            $data = $service->data_ga->get('ga:' . $projectId, $from, $to, $metrics, array('dimensions' => $dimensions));

            $i = 0;
            $output->writeln("import du " . $nextDate->format('Y-m-d'));
            if(!count($data["rows"])){
                echo "Rien ... \n";
            }
            else{
                foreach ($data["rows"] as $row) {
                    $o = $rep->findOneBy(array('orderId' => $row[0]));
                    if (is_null($o)) {

                        $oDate = new \DateTime($row[4] . "-" . $row[5] . "-" . $row[6]);

                        $o = new OrderTracking();
                        $o->setOrderId($row[0]);
                        $o->setCampaign($row[1] . " / " . $row[2]);
                        $o->setMedium($row[3]);
                        $o->setDateOrder($oDate);
                        $o->setOrderStatus(0);
                        $o->setOrderPrice(0);

                        $em->persist($o);

                    }

                }
            }
            $i++;


            $em->flush();

            $output->writeln($i . " commandes importées pour le " . $nextDate->format('Y-m-d'));
            sleep(1);
            if ($nextDate->format("Ymd") == $today->format("Ymd")) {
                exit;
            }
            $nextDate->add(new \DateInterval("P1D"));
        }



    }
}