<?php

namespace Ocarat\CoreBundle\Controller;

use Ocarat\CoreBundle\Entity\ProductSearch;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{

    /**
     * @Route("/product", name="product", options={"expose"=true})
     * @Route("/product/search/{productSearch}", name="product_search", options={"expose"=true})
     * @Template()
     */
    public function indexAction(Request $request, ProductSearch $productSearch = null)
    {
        $legacyManager = $this->get('ocarat_core.legacyManager');

        $categories = $legacyManager->getCategories();
        $attributes = $legacyManager->getAttributes();


        $productSearches = $this->getDoctrine()->getRepository('OcaratCoreBundle:ProductSearch')->findAll();

        $orderProducts = array();

        $dateFrom = $request->get('dateFrom');
        $dateTo = $request->get('dateTo');
        if(!$dateFrom){
            $dateFrom = new \DateTime();
            $dateFrom = $dateFrom->sub(new \DateInterval('P1Y'));
        }
        else{
            $dateFrom = new \DateTime($dateFrom);
        }
        if(!$dateTo){
            $dateTo = new \DateTime();
        }
        else{
            $dateTo = new \DateTime($dateTo);
        }

        $graph = array('ca' => 0, 'total' => 0, 'month' => 0);

        if(! is_null($productSearch) ) {

            $graph = $this->get('ocarat_core.legacyManager')->getSalesByMonth($productSearch, $dateFrom, $dateTo);
            $orderProducts = $this->get('ocarat_core.legacyManager')->getSales($productSearch, $dateFrom, $dateTo);

        }

        return array(
            "categories" => $categories,
            "attributes" => $attributes,
            "productSearch" => $productSearch,
            "productSearches" => $productSearches,
            "dateFrom" => $dateFrom->format('Y-m-d'),
            "dateTo" => $dateTo->format('Y-m-d'),
            "orderProducts" => $orderProducts,
            "graphCa" => $graph['ca'],
            "graphArticles" => $graph['total'],
            "graphDate" => $graph['month'],
        );
    }

    /**
     * @Route("/product/delete/{productSearch}", name="product_del", options={"expose"=true})
     */
    public function delAction(Request $request, ProductSearch $productSearch)
    {
        $this->getDoctrine()->getManager()->remove($productSearch);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirect($this->generateUrl('product'));
    }

    /**
     * @Route("/product/edit", name="product_edit")
     */
    public function addAction(Request $request)
    {

        if ($request->getMethod() === "POST") {

            $include = $request->get('include');
            $exclude = $request->get('exclude');
            $name = $request->get('searchName');
            $inc_attr = $inc_cat = $exc_attr = $exc_cat = array();
            if (count($include)) {
                foreach ($include as $inc) {
                    if (strpos($inc, 'cat_') !== false) {
                        $inc_cat[] = intval(str_replace("cat_", '', $inc));
                    }
                    if (strpos($inc, 'attr_') !== false) {
                        $inc_attr[] = intval(str_replace("attr_", '', $inc));
                    }
                }
            }
            if (count($exclude)) {
                foreach ($exclude as $exc) {
                    if (strpos($exc, 'cat_') !== false) {
                        $exc_cat[] = intval(str_replace("cat_", '', $exc));
                    }
                    if (strpos($exc, 'attr_') !== false) {
                        $exc_attr[] = intval(str_replace("attr_", '', $exc));
                    }
                }
            }

            $id = $request->get('id');

            $productSearchManager = $this->get('ocarat.product.search.manager');
            $productSearchManager->create($name, $inc_attr, $inc_cat, $exc_attr, $exc_cat, $id);

            return $this->redirect($this->generateUrl('product'));
        }
    }

}