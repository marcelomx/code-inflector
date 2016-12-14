<?php

class MockEntity
{
    /**
     * @var string
     */
    protected $test_field;

    /**
     * @var string
     */
    protected $test_field2;

    /**
     * @var  string
     */
    protected $test_field3;

    /**
     * @var string
     */
    protected $not_mapped_attribute;

    /**
     * @var MockFooEntity
     */
    protected $mock_foo;

    /**
     * @var MockEntity[]
     */
    protected $mock_bars;

    /**
     * @var MockEntity
     */
    protected $mock_parent;

    /**
     * @var MockGroup[]
     */
    protected $mock_groups;

    /**
     * @return MockFooEntity
     */
    public function getMockFoo()
    {
        return $this->mock_foo;
    }

    /**
     * @param MockFooEntity $mock_foo
     */
    public function setMockFoo($mock_foo)
    {
        $this->mock_foo = $mock_foo;
    }

    /**
     * @return MockEntity[]
     */
    public function getMockBars()
    {
        return $this->mock_bars;
    }

    /**
     * @param MockEntity[] $mock_bars
     */
    public function setMockBars($mock_bars)
    {
        $this->mock_bars = $mock_bars;
    }

    /**
     * @param MockEntity $mock_bar
     */
    public function addMockBar(MockEntity $mock_bar)
    {
        $this->mock_bars[] = $mock_bar;
        $mock_bar->setMockParent($this);
    }

    /**
     * @return MockEntity
     */
    public function getMockParent()
    {
        return $this->mock_parent;
    }

    /**
     * @param MockEntity $mock_parent
     */
    public function setMockParent($mock_parent)
    {
        $this->mock_parent = $mock_parent;
    }

    /**
     * @return MockGroup[]
     */
    public function getMockGroups()
    {
        return $this->mock_groups;
    }

    /**
     * @param MockGroup[] $mock_groups
     */
    public function setMockGroups($mock_groups)
    {
        $this->mock_groups = $mock_groups;
    }

    /**
     * @param MockGroup $mock_group
     */
    public function addMockGroup(MockGroup $mock_group)
    {
        $this->mock_groups[] = $mock_group;
    }

    /**
     * @return string
     */
    public function fooNotMappedAttribute()
    {
        $this->not_mapped_attribute = $this->mock_groups;

        return $this->test_field;
    }

    /**
     * @param $oneToManyEntity
     */
    public function addOneToManyEntity($oneToManyEntity)
    {
        $this->mock_bars[] = $oneToManyEntity;
    }
}