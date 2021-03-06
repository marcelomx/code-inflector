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
        $query = 'SELECT m.test_field, f.foo_name' .
                 'FROM MockEntity m ' .
                 'INNER JOIN m.mock_foo f';

        return $this->createQueryBuilder($query);
    }

    /**
     * @return string
     */
    public function getAllWithBars()
    {
        $query = 'SELECT m.test_field, b.test_field2, p.test_field3' .
                 'FROM MockEntity m ' .
                 'INNER JOIN m.mock_bars b ' .
                 'INNER JOIN b.mock_parent p';

        return $this->createQueryBuilder($query);
    }

    /**
     * @return string
     */
    public function getAllWithMockers()
    {
        $query = 'SELECT m.test_field, g.group_name' .
                 'FROM MockEntity m ' .
                 'INNER JOIN m.mock_mockers g';

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