<?php

namespace Ocarat\CoreBundle\Command;

use Ocarat\CoreBundle\Entity\CampaignCost;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ocarat\CoreBundle\Entity\OrderTracking;

class CostCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ga:getCosts')
            ->setDescription('recuperation des couts GA / Adwords');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        for ($j = 0; $j < 2; $j++) {
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

            $rep = $em->getRepository("OcaratCoreBundle:CampaignCost");

            $from = date('Y-m-d', time()-$j*60*60*24);
            $to = date('Y-m-d', time()-$j*60*60*24);

            $metrics = 'ga:adCost';
            $dimensions = 'ga:campaign,ga:year,ga:month,ga:day';
            $data = $service->data_ga->get('ga:' . $projectId, $from, $to, $metrics, array('dimensions' => $dimensions));

            $i = 0;
            $output->writeln("import du ".$from);
            foreach ($data["rows"] as $row) {
                $oDate = new \DateTime($row[1] . "-" . $row[2] . "-" . $row[3]);
                $o = $rep->findOneBy(array('dateFrom' => $oDate, 'dateTo'=> $oDate, 'campaign' => "google / cpc", 'medium' =>$row[0]));
                if (is_null($o)) {
                    $o = new CampaignCost();
                    $o->setDateFrom($oDate);
                    $o->setDateTo($oDate);
                    $o->setCampaign("google / cpc");
                    $o->setMedium($row[0]);
                    $o->setCost($row[4]);

                }
                else{
                    $o->setCost($row[4]);
                }

                $em->persist($o);

            }

            $em->flush();


            sleep(1);

        }
    }
}