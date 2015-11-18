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
    protected $test_field;
    protected $test_field2;
    protected $one_to_bar;
    protected $many_to_foo;
    protected $many_to_many_baz;
    protected $not_mapped_property;

    /**
     * @return mixed
     */
    public function getNotMappedProperty()
    {
        return $this->not_mapped_property;
    }

    public function getNoExistentInvalidProperty()
    {
        return $this->not_mapped_and_inexistent_property;
    }
}