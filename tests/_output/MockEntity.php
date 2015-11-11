<?php

class MockEntity
{
    protected $testField;
    protected $oneToManyEntity;
    protected $manyToOneEntity;
    protected $manyToManyEntity;
    protected $notMappedAttribute;
    protected $inversedField;
    protected $inversedOneField;
    protected $inversedManyField;

    public function fooNotMappedAttribute()
    {
        $this->notMappedAttribute = $this->manyToManyEntity;
        return $this->testField;
    }
}