<?php return array (
  'MockEntity' => 
  array (
    'repositoryClass' => 'MockEntityRepository',
    'fields' => 
    array (
      'test_field' => 
      array (
        'type' => 'string',
        'column' => 'test_field',
      ),
      'test_field2' => 
      array (
        'type' => 'string',
      ),
    ),
    'oneToMany' => 
    array (
      'one_to_many_entity' => 
      array (
        'inversedBy' => 'inversed_field',
        'mappedBy' => 'mapped_field',
        'orderBy' => 'order_field',
        'indexedBy' => 'indexed_field',
      ),
    ),
    'manyToOne' => 
    array (
      'many_to_one_entity' => 
      array (
        'inversedBy' => 'inversed_one_field',
        'mappedBy' => 'mapped_one_field',
      ),
    ),
    'manyToMany' => 
    array (
      'many_to_many_entity' => 
      array (
        'inversedBy' => 'inversed_many_field',
        'mappedBy' => 'mapped_many_field',
      ),
    ),
  ),
);