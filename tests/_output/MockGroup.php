<?php

class MockGroup
{
    /**
     * @var string
     */
    protected $groupName;

    /**
     * @var string
     */
    protected $groupRole;

    /**
     * @var MockEntity[]
     */
    protected $mockers;

    /**
     * @return string
     */
    public function getGroupName()
    {
        return $this->groupName;
    }

    /**
     * @param string $groupName
     */
    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;
    }

    /**
     * @return string
     */
    public function getGroupRole()
    {
        return $this->groupRole;
    }

    /**
     * @param string $groupRole
     */
    public function setGroupRole($groupRole)
    {
        $this->groupRole = $groupRole;
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