<?php return array (
  'MockEntity' => 
  array (
    'repositoryClass' => 'MockEntityRepository',
    'fields' => 
    array (
      'testField' => 
      array (
        'type' => 'string',
        'column' => 'test_field',
      ),
      'testField2' => 
      array (
        'type' => 'string',
        'column' => 'test_field2',
      ),
    ),
    'oneToMany' => 
    array (
      'oneToManyEntity' => 
      array (
        'inversedBy' => 'inversedField',
        'mappedBy' => 'mappedField',
        'orderBy' => 'orderField',
        'indexedBy' => 'indexedField',
      ),
    ),
    'manyToOne' => 
    array (
      'manyToOneEntity' => 
      array (
        'inversedBy' => 'inversedOneField',
        'mappedBy' => 'mappedOneField',
      ),
    ),
    'manyToMany' => 
    array (
      'manyToManyEntity' => 
      array (
        'inversedBy' => 'inversedManyField',
        'mappedBy' => 'mappedManyField',
      ),
    ),
  ),
);