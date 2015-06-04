<?php

namespace Ocarat\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CampaignCost
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ProductSearch
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="attributes", type="json_array")
     */
    private $attributes;

    /**
     * @var float
     *
     * @ORM\Column(name="categories", type="json_array")
     */
    private $categories;


}
