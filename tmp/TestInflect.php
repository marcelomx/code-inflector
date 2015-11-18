<?php

/**
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */ 
class TestInflect
{
    protected $dashed_property;
    protected $camelProperty;

    public function getDashedProperty()
    {
        return $this->dashed_property;
    }

    public function getCamelProperty()
    {
        return $this->camelProperty;
    }
}