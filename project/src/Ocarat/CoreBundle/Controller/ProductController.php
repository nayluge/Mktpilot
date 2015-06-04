<?php

namespace Ocarat\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends Controller
{

    /**
     * @Route("/product", name="product")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $legacyManager = $this->get('ocarat_core.legacyManager');

        $categories = $legacyManager->getCategories();
        $attributes = $legacyManager->getAttributes();

        $orderProducts = array();

        if ($request->getMethod() === "POST") {
            $include = $request->get('include');
            $exclude = $request->get('exclude');
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
        }

        return array(
            "categories" => $categories,
            "attributes" => $attributes
        );
    }

}