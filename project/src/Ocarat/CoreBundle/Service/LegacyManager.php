<?php

namespace Ocarat\CoreBundle\Service;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Ocarat\CoreBundle\Entity\ProductSearch;
use Symfony\Component\DependencyInjection\ContainerAware;

class LegacyManager
{

    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(ManagerRegistry $doctrine)
    {

        $this->em = $doctrine->getManager('default');
        $this->emoc = $doctrine->getManager('site');
    }

    public function getAttributes()
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id_feature_value', 'id_feature_value');
        $rsm->addScalarResult('name', 'name');
        $query = $this->emoc
            ->createNativeQuery('SELECT LPAD(id_feature_value,10, 0) as id_feature_value, value as name FROM ps_feature_value_lang WHERE id_lang = ? and value!="" ',
                $rsm);

        $query->setParameter(1, 1);
        $attributes = $query->useResultCache(true, 3600, 'getAttributesProduct')->getResult();
        $ret = array();
        foreach ($attributes as $attr) {
            $ret[$attr["id_feature_value"]] = $attr;
        }

        return $ret;
    }

    public function getCategories()
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id_category', 'id_category');
        $rsm->addScalarResult('name', 'name');
        $query = $this->emoc
            ->createNativeQuery('SELECT DISTINCT name, LPAD(id_category,10, 0) as id_category FROM ps_category_lang WHERE id_lang = ? and name!="" GROUP BY name',
                $rsm);

        $query->setParameter(1, 1);
        $categories = $query->useResultCache(true, 3600, 'getCategoriesProduct')->getResult();
        $ret = array();
        foreach ($categories as $cat) {
            $ret[$cat["id_category"]] = $cat;
        }

        return $ret;
    }

    public function getSalesByMonth(ProductSearch $productSearch, \DateTime $from, \DateTime $to)
    {
        if($from->diff($to)->invert === 1)
        {
            $tmp = $to;
            $to =$from;
            $from = $tmp;
        }

        $stats = array();

        while($from->diff($to)->invert === 0)
        {
            $step = clone $from;
            $step->modify('last day of this month');
            if($step->diff($to)->invert === 1)
            {
                $step = clone $to;
            }

            $stats['month'][] = $from->format('m / Y');

            $res = $this->getSales($productSearch, $from, $step, "YEAR(date_add)");

            $stats['ca'][] = $res[0]["ca"];
            $stats['total'][] = $res[0]["total"];

            $from = $step->add(new \DateInterval('P1D'));
        }

        return $stats;

    }

    public function getSales(ProductSearch $productSearch, \DateTime $from, \DateTime $to, $group = "od.product_name")
    {


        $i = 0;
        $sub = "";
        foreach($productSearch->getAttributes() as $a){
            $sub.=" INNER JOIN ps_feature_product t$i ON t$i.id_product = od.product_id AND t$i.id_feature_value = $a";
            $i++;
        }

        foreach($productSearch->getCategories() as $a){
            $sub.=" INNER JOIN ps_category_product t$i ON t$i.id_product = od.product_id AND t$i.id_category = $a";
            $i++;
        }

        $where ="o.date_add >= '".$from->format('Y-m-d')." 00:00:00'";
        $where .="AND o.date_add <= '".$to->format('Y-m-d')." 23:59:59'";
        foreach($productSearch->getExcludedAttributes() as $a){
            $where.=" AND NOT EXISTS( SELECT * FROM ps_feature_product t$i WHERE t$i.id_product = od.product_id AND t$i.id_feature_value = $a)";
            $i++;
        }
        foreach($productSearch->getExcludedCategories() as $a){
            $where.=" AND NOT EXISTS( SELECT * FROM ps_category_product t$i WHERE t$i.id_product = od.product_id AND t$i.id_category = $a)";
            $i++;
        }

        $badStatus = $this->em->getRepository("OcaratCoreBundle:Status")->findBy(array('usedForAmount' => false));
        $aBadStatus = array();
        foreach($badStatus as $status){
            $aBadStatus[] = $status->getId();
        }
        $aBadStatus[] =0;
        $where.=' AND (SELECT id_order_state
		      FROM ps_order_history h
              WHERE h.id_order = o.id_order
              ORDER BY date_add DESC, id_order_history DESC LIMIT 1) NOT IN ('.implode(",", $aBadStatus).')';

        $q = "SELECT
              od.product_name,
              od.product_id as product_id,
              GROUP_CONCAT(DISTINCT od.id_order) as orders,
              sum((od.product_price * (100 - od.reduction_percent) / 100 - od.reduction_amount) * ((100 - (o.total_discounts*100/o.total_products_wt))/100)) as ca,
              sum(od.product_quantity) as total,

              count(DISTINCT od.id_order) as nb_order
              FROM ps_order_detail od
              INNER JOIN ps_orders o
              ON o.id_order = od.id_order
              $sub
              WHERE
              $where
              GROUP BY
              $group
              ORDER BY ca DESC";

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('product_id', 'product_id');
        $rsm->addScalarResult('product_name', 'product_name');
        $rsm->addScalarResult('orders', 'orders');
        $rsm->addScalarResult('ca', 'ca');
        $rsm->addScalarResult('total', 'total');
        $rsm->addScalarResult('nb_order', 'nb_order');

        $query = $this->emoc
            ->createNativeQuery($q,
                $rsm);

        return $orderProducts = $query->useResultCache(true, 3600, 'getProductResult'.md5($sub.$where.$group))->getResult();
    }

}