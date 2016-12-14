<?php

class MockFooEntity
{
    /**
     * @var  string
     */
    protected $fooName;

    /**
     * @var  string
     */
    protected $barAt;

    /**
     * @var MockEntity
     */
    protected $mockOwner;

    /**
     * @return string
     */
    public function getFooName()
    {
        return $this->fooName;
    }

    /**
     * @param string $fooName
     */
    public function setFooName($fooName)
    {
        $this->fooName = $fooName;
    }

    /**
     * @return string
     */
    public function getBarAt()
    {
        return $this->barAt;
    }

    /**
     * @param string $barAt
     */
    public function setBarAt($barAt)
    {
        $this->barAt = $barAt;
    }

    /**
     * @return MockEntity
     */
    public function getMockOwner()
    {
        return $this->mockOwner;
    }

    /**
     * @param MockEntity $mockOwner
     */
    public function setMockOwner($mockOwner)
    {
        $this->mockOwner = $mockOwner;
    }
}