<?php

namespace Ocarat\CoreBundle\Controller;

use Doctrine\ORM\Query\ResultSetMapping;
use Ocarat\CoreBundle\Entity\Status;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class StatusController extends Controller
{
    /**
     * @Route("/status/", name="status")
     * @Template()
     */
    public function listAction()
    {
        $emoc = $this->getDoctrine()->getManager('site');

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id_order_state', 'id_order_state');
        $rsm->addScalarResult('name', 'name');

        $query = $emoc->createNativeQuery('SELECT id_order_state, name FROM ps_order_state_lang WHERE id_lang= 3', $rsm);

        $statuses = $query->getResult();

        $em = $this->getDoctrine()->getManager();
        foreach ($statuses as $status)
        {
            $o = $em->getRepository("OcaratCoreBundle:Status")->findOneBy(array("statusId" => $status["id_order_state"]));
            if (is_null($o))
            {
                $o = new Status();
                $o->setName($status["name"]);
                $o->setStatusId($status["id_order_state"]);
                $o->setUsedForAmount(true);
                $em->persist($o);
                $em->flush();
            }
        }

        $statuses = $em->getRepository("OcaratCoreBundle:Status")->findAll();



        return array(
            'statuses' => $statuses,
            'page' => 'status'
        );
    }

    /**
     * @Route("/status/update/{status}", name="status_update")
     */
    public function updateAction(Status $status)
    {
       $status->setUsedForAmount(!$status->getUsedForAmount());
        $em = $this->getDoctrine()->getManager();
        $em->persist($status);
        $em->flush();
        echo "1"; exit;

    }

}
