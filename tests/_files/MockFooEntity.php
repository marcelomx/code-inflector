<?php

class MockFooEntity
{
    /**
     * @var  string
     */
    protected $foo_name;

    /**
     * @var  string
     */
    protected $bar_at;

    /**
     * @var MockEntity
     */
    protected $mock_owner;

    /**
     * @return string
     */
    public function getFooName()
    {
        return $this->foo_name;
    }

    /**
     * @param string $foo_name
     */
    public function setFooName($foo_name)
    {
        $this->foo_name = $foo_name;
    }

    /**
     * @return string
     */
    public function getBarAt()
    {
        return $this->bar_at;
    }

    /**
     * @param string $bar_at
     */
    public function setBarAt($bar_at)
    {
        $this->bar_at = $bar_at;
    }

    /**
     * @return MockEntity
     */
    public function getMockOwner()
    {
        return $this->mock_owner;
    }

    /**
     * @param MockEntity $mockOwner
     */
    public function setMockOwner($mockOwner)
    {
        $this->mock_owner = $mockOwner;
    }
}