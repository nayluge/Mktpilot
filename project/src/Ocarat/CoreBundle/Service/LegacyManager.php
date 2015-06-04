<?php

namespace Ocarat\CoreBundle\Service;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
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

    public function getAttributes(){
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id_feature_value', 'id_feature_value');
        $rsm->addScalarResult('name', 'name');
        $query = $this->emoc
            ->createNativeQuery('SELECT LPAD(id_feature_value,10, 0) as id_feature_value, value as name FROM ps_feature_value_lang WHERE id_lang = ? and value!="" ', $rsm);

        $query->setParameter(1, 1);
        $attributes = $query->getResult();
        return $attributes;
    }

    public function getCategories(){
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id_category', 'id_category');
        $rsm->addScalarResult('name', 'name');
        $query = $this->emoc
            ->createNativeQuery('SELECT DISTINCT name, LPAD(id_category,10, 0) as id_category FROM ps_category_lang WHERE id_lang = ? and name!="" GROUP BY name', $rsm);

        $query->setParameter(1, 1);
        $categories = $query->getResult();
        return $categories;
    }
}