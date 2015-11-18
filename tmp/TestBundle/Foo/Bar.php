<?php

 namespace TestBundle\Foo;

/**
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */ 
class Bar 
{
    /**
     * @var TestBundle\Entity\BundleEntity
     */
    protected $bundle_entity;

    /**
     * @return TestBundle\Entity\BundleEntity
     */
    public function getBundleEntity()
    {
        return $this->bundle_entity;
    }
}