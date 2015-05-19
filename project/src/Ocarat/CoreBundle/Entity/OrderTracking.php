<?php

namespace Ocarat\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderTracking
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Ocarat\CoreBundle\Repository\OrderTrackingRepository")
 */
class OrderTracking
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
     * @ORM\Column(name="orderId", type="integer")
     */
    private $orderId;

    /**
     * @var string
     *
     * @ORM\Column(name="campaign", type="string")
     */
    private $campaign;

    /**
     * @var string
     *
     * @ORM\Column(name="medium", type="string")
     */
    private $medium;

    /**
     * @var string
     *
     * @ORM\Column(name="dateOrder", type="date")
     */
    private $dateOrder;

    /**
     * @var string
     *
     * @ORM\Column(name="orderStatus", type="integer")
     */
    private $orderStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="orderPrice", type="float")
     */
    private $orderPrice;

    /**
     * @ORM\Column(name="campaignHistory",type="json_array")
     * @var array
     */
    protected $campaignHistory = array();




    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set campaign
     *
     * @param string $campaign
     * @return OrderTracking
     */
    public function setCampaign($campaign)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * Get campaign
     *
     * @return string 
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * Set medium
     *
     * @param string $medium
     * @return OrderTracking
     */
    public function setMedium($medium)
    {
        $this->medium = $medium;

        return $this;
    }

    /**
     * Get medium
     *
     * @return string 
     */
    public function getMedium()
    {
        return $this->medium;
    }

    /**
     * Set dateOrder
     *
     * @param \DateTime $dateOrder
     * @return OrderTracking
     */
    public function setDateOrder($dateOrder)
    {
        $this->dateOrder = $dateOrder;

        return $this;
    }

    /**
     * Get dateOrder
     *
     * @return \DateTime 
     */
    public function getDateOrder()
    {
        return $this->dateOrder;
    }

    /**
     * Set orderId
     *
     * @param integer $orderId
     * @return OrderTracking
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * Get orderId
     *
     * @return integer 
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Set orderStatus
     *
     * @param integer $orderStatus
     * @return OrderTracking
     */
    public function setOrderStatus($orderStatus)
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }

    /**
     * Get orderStatus
     *
     * @return integer 
     */
    public function getOrderStatus()
    {
        return $this->orderStatus;
    }

    /**
     * Set orderPrice
     *
     * @param float $orderPrice
     * @return OrderTracking
     */
    public function setOrderPrice($orderPrice)
    {
        $this->orderPrice = $orderPrice;

        return $this;
    }

    /**
     * Get orderPrice
     *
     * @return float 
     */
    public function getOrderPrice()
    {
        return $this->orderPrice;
    }

    /**
     * Set campaignHistory
     *
     * @param array $campaignHistory
     * @return OrderTracking
     */
    public function setCampaignHistory($campaignHistory)
    {
        $this->campaignHistory = $campaignHistory;

        return $this;
    }

    /**
     * Get campaignHistory
     *
     * @return array 
     */
    public function getCampaignHistory()
    {
        return $this->campaignHistory;
    }
}
