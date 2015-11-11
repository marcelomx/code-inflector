<?php

class MockEntity
{
    protected $test_field;
    protected $one_to_many_entity;
    protected $many_to_one_entity;
    protected $many_to_many_entity;
    protected $not_mapped_attribute;
    protected $inversed_field;
    protected $inversed_one_field;
    protected $inversed_many_field;

    public function fooNotMappedAttribute()
    {
        $this->not_mapped_attribute = $this->many_to_many_entity;
        return $this->test_field;
    }
}