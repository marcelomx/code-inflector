<?php

class MockGroup
{
    /**
     * @var string
     */
    protected $group_name;

    /**
     * @var string
     */
    protected $group_role;

    /**
     * @var MockEntity[]
     */
    protected $mockers;

    /**
     * @return string
     */
    public function getGroupName()
    {
        return $this->group_name;
    }

    /**
     * @param string $group_name
     */
    public function setGroupName($group_name)
    {
        $this->group_name = $group_name;
    }

    /**
     * @return string
     */
    public function getGroupRole()
    {
        return $this->group_role;
    }

    /**
     * @param string $group_role
     */
    public function setGroupRole($group_role)
    {
        $this->group_role = $group_role;
    }

    /**
     * @return MockEntity[]
     */
    public function getMockers()
    {
        return $this->mockers;
    }

    /**
     * @param MockEntity[] $mockers
     */
    public function setMockers($mockers)
    {
        $this->mockers = $mockers;
    }

    /**
     * @param MockEntity $mocker
     *
     * @return MockGroup
     */
    public function addMocker($mocker)
    {
        $this->mockers[] = $mocker;

        return $this;
    }
}