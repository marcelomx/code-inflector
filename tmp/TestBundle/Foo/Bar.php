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
    protected $bundleEntity;

    /**
     * @return TestBundle\Entity\BundleEntity
     */
    public function getBundleEntity()
    {
        return $this->bundleEntity;
    }
}