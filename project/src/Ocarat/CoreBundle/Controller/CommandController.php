<?php

namespace Ocarat\CoreBundle\Controller;

use Ocarat\CoreBundle\Entity\OrderTracking;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\Query\ResultSetMapping;

class CommandController extends Controller
{

    /**
     * @Route("/commandHistory", name="command_history")
     * @Template()
     */
    public function commandHistoryAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $orderRepo = $em->getRepository('OcaratCoreBundle:OrderTracking');
        $orders = $orderRepo->findBy(
            array(),
            array(
                "orderId" => "DESC"
            ),
            1000
        );

        return array(
            'page' => 'history',
            'orders' => $orders
        );

        var_dump($orders);
    }
}
