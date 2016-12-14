<?php

/**
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */ 
class MockEntityRepository 
{
    /**
     * @return string
     */
    public function getAllWithFoo()
    {
        $query = 'SELECT m.testField, f.fooName' .
                 'FROM MockEntity m ' .
                 'INNER JOIN m.mockFoo f';

        return $this->createQueryBuilder($query);
    }

    /**
     * @return string
     */
    public function getAllWithBars()
    {
        $query = 'SELECT m.testField, b.testField2, p.testField3' .
                 'FROM MockEntity m ' .
                 'INNER JOIN m.mockBars b ' .
                 'INNER JOIN b.mockParent p';

        return $this->createQueryBuilder($query);
    }

    /**
     * @return string
     */
    public function getAllWithMockers()
    {
        $query = 'SELECT m.testField, g.groupName' .
                 'FROM MockEntity m ' .
                 'INNER JOIN m.mockMockers g';

        return $this->createQueryBuilder($query);
    }

    /**
     * @param string $alias
     *
     * @return string
     */
    protected function createQueryBuilder($alias = '')
    {
        return new static($alias);
    }

    /**
     * @param $sql
     *
     * @return array
     */
    protected function query($sql)
    {
        return ['sql' => $sql, 'results' => []];
    }
}