<?php

namespace Ocarat\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Visit
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ocarat\CoreBundle\Repository\VisitRepository")
 */
class Visit
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
     * @ORM\Column(name="dateVisit", type="date")
     */
    private $dateVisit;

    /**
     * @var integer
     *
     * @ORM\Column(name="nbVisit", type="integer")
     */
    private $nbVisit;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDateVisit()
    {
        return $this->dateVisit;
    }

    /**
     * @param string $dateVisit
     */
    public function setDateVisit($dateVisit)
    {
        $this->dateVisit = $dateVisit;
    }

    /**
     * @return int
     */
    public function getNbVisit()
    {
        return $this->nbVisit;
    }

    /**
     * @param int $nbVisit
     */
    public function setNbVisit($nbVisit)
    {
        $this->nbVisit = $nbVisit;
    }


}
