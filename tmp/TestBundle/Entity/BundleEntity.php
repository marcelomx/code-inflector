<?php

namespace TestBundle\Entity;

/**
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */
class BundleEntity
{
    protected $name;
    protected $id;
    protected $testField;
    protected $testField2;
    protected $one_to_bar;
    protected $many_to_foo;
    protected $manyToManyBaz;
    protected $notMappedProperty;

    /**
     * @return mixed
     */
    public function getNotMappedProperty()
    {
        return $this->notMappedProperty;
    }

    public function getNoExistentInvalidProperty()
    {
        return $this->notMappedAndInexistentProperty;
    }
}