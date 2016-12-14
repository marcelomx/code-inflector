<?php

class MockEntity
{
    /**
     * @var string
     */
    protected $testField;

    /**
     * @var string
     */
    protected $testField2;

    /**
     * @var  string
     */
    protected $testField3;

    /**
     * @var string
     */
    protected $notMappedAttribute;

    /**
     * @var MockFooEntity
     */
    protected $mockFoo;

    /**
     * @var MockEntity[]
     */
    protected $mockBars;

    /**
     * @var MockEntity
     */
    protected $mockParent;

    /**
     * @var MockGroup[]
     */
    protected $mockGroups;

    /**
     * @return MockFooEntity
     */
    public function getMockFoo()
    {
        return $this->mockFoo;
    }

    /**
     * @param MockFooEntity $mockFoo
     */
    public function setMockFoo($mockFoo)
    {
        $this->mockFoo = $mockFoo;
    }

    /**
     * @return MockEntity[]
     */
    public function getMockBars()
    {
        return $this->mockBars;
    }

    /**
     * @param MockEntity[] $mockBars
     */
    public function setMockBars($mockBars)
    {
        $this->mockBars = $mockBars;
    }

    /**
     * @param MockEntity $mock_bar
     */
    public function addMockBar(MockEntity $mock_bar)
    {
        $this->mockBars[] = $mock_bar;
        $mock_bar->setMockParent($this);
    }

    /**
     * @return MockEntity
     */
    public function getMockParent()
    {
        return $this->mockParent;
    }

    /**
     * @param MockEntity $mockParent
     */
    public function setMockParent($mockParent)
    {
        $this->mockParent = $mockParent;
    }

    /**
     * @return MockGroup[]
     */
    public function getMockGroups()
    {
        return $this->mockGroups;
    }

    /**
     * @param MockGroup[] $mockGroups
     */
    public function setMockGroups($mockGroups)
    {
        $this->mockGroups = $mockGroups;
    }

    /**
     * @param MockGroup $mock_group
     */
    public function addMockGroup(MockGroup $mock_group)
    {
        $this->mockGroups[] = $mock_group;
    }

    /**
     * @return string
     */
    public function fooNotMappedAttribute()
    {
        $this->notMappedAttribute = $this->mockGroups;

        return $this->testField;
    }

    /**
     * @param $oneToManyEntity
     */
    public function addOneToManyEntity($oneToManyEntity)
    {
        $this->mockBars[] = $oneToManyEntity;
    }
}