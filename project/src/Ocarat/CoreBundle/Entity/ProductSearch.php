<?php

namespace Ocarat\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductSearch
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
     * @var array
     *
     * @ORM\Column(name="categories", type="json_array")
     */
    private $categories;

    /**
     * @var array
     *
     * @ORM\Column(name="attributes", type="json_array")
     */
    private $attributes;

    /**
     * @var array
     *
     * @ORM\Column(name="excludedCategories", type="json_array")
     */
    private $excludedCategories;

    /**
     * @var array
     *
     * @ORM\Column(name="excludedAttributes", type="json_array")
     */
    private $excludedAttributes;


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
     * Set name
     *
     * @param string $name
     *
     * @return ProductSearch
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set categories
     *
     * @param array $categories
     *
     * @return ProductSearch
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * Get categories
     *
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Get categories Front
     *
     * @return array
     */
    public function getCategoriesFront()
    {
        $ret = array();
        foreach($this->categories as $k => $category) {
            $ret[$k] = str_pad($category, 10, 0, STR_PAD_LEFT);
        }
        return $ret;
    }

    /**
     * Set attributes
     *
     * @param array $attributes
     *
     * @return ProductSearch
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Get attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get attributes Front
     *
     * @return array
     */
    public function getAttributesFront()
    {
        $ret = array();
        foreach($this->attributes as $k => $attribute) {
            $ret[$k] = str_pad($attribute, 10, 0, STR_PAD_LEFT);
        }
        return $ret;
    }

    /**
     * Set excludedCategories
     *
     * @param array $excludedCategories
     *
     * @return ProductSearch
     */
    public function setExcludedCategories($excludedCategories)
    {
        $this->excludedCategories = $excludedCategories;

        return $this;
    }

    /**
     * Get excludedCategories
     *
     * @return array
     */
    public function getExcludedCategories()
    {
        return $this->excludedCategories;
    }

    /**
     * Get Excluded categories Front
     *
     * @return array
     */
    public function getExcludedCategoriesFront()
    {
        $ret = array();
        foreach($this->excludedCategories as $k => $category) {
            $ret[$k] = str_pad($category, 10, 0, STR_PAD_LEFT);
        }
        return $ret;
    }

    /**
     * Set excludedAttributes
     *
     * @param array $excludedAttributes
     *
     * @return ProductSearch
     */
    public function setExcludedAttributes($excludedAttributes)
    {
        $this->excludedAttributes = $excludedAttributes;

        return $this;
    }

    /**
     * Get excludedAttributes
     *
     * @return array
     */
    public function getExcludedAttributes()
    {
        return $this->excludedAttributes;
    }

    /**
     * Get attributes Front
     *
     * @return array
     */
    public function getExcludedAttributesFront()
    {
        $ret = array();
        foreach($this->excludedAttributes as $k => $attribute) {
            $ret[$k] = str_pad($attribute, 10, 0, STR_PAD_LEFT);
        }
        return $ret;
    }
    
}

