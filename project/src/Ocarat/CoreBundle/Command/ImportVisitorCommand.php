<?php

namespace Ocarat\CoreBundle\Command;

use Ocarat\CoreBundle\Entity\Visit;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ocarat\CoreBundle\Entity\OrderTracking;

class ImportVisitorCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ga:getVisits')
            ->setDescription('recuperation des visites GA');
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
        $repoVisit = $em->getRepository('OcaratCoreBundle:Visit');

        $nextDate = new \DateTime(date("Y-m-d", time()-60*60*24*2));
        $today = new \DateTime();

        for ($j = 0; $j < 3; $j++) {

            $from = date('Y-m-d', $nextDate->getTimestamp()); // 2 days
            $to = date('Y-m-d', $nextDate->getTimestamp()); // today
            $metrics = 'ga:visits';
            $dimensions = 'ga:year,ga:month,ga:day';
            $data = $service->data_ga->get('ga:' . $projectId, $from, $to, $metrics, array('dimensions' => $dimensions));

            $i = 0;
            $output->writeln("import du " . $nextDate->format('Y-m-d'));

            if(!count($data["rows"])){
                echo "Rien ... \n";
            }
            else{

                $visit = $repoVisit->findOneBy(
                    array(
                        "dateVisit" => $nextDate
                    )
                );
                if (is_null($visit)) {
                    $visit = new Visit();
                }

                $visit->setDateVisit($nextDate);
                $visit->setNbVisit($data["rows"][0][3]);

                $em->persist($visit);

            }


            $em->flush();

            $output->writeln($data["rows"][0][3] . " visites importées pour le " . $nextDate->format('Y-m-d'));
            sleep(1);
            if ($nextDate->format("Ymd") == $today->format("Ymd")) {
                exit;
            }
            $nextDate->add(new \DateInterval("P1D"));
        }



    }
}