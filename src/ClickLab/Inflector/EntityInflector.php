<?php

namespace ClickLab\Inflector;

use Symfony\Component\Yaml\Yaml as YmlParser;

class EntityInflector extends FileInflector
{
    /** @var  YmlParser */
    protected $ymlParser;
    protected $file;
    protected $originalMapping;
    protected $entityMapping;
    protected $entityClass;
    protected $classMapping;
    protected $repositoryClass;
    /** @var  ClassInflector */
    protected $classInflector;
    /** @var  ClassInflector */
    protected $repositoryInflector;
    protected $changedFields = array();

    /**
     * @param string $mappingFile
     * @param YmlParser $ymlParser
     */
    public function __construct($mappingFile, $ymlParser = null)
    {
        parent::__construct($mappingFile);
        $this->ymlParser = $ymlParser;
        $this->loadEntity();
    }

    /**
     * @return void
     */
    public function loadEntity()
    {
        $this->originalMapping = $this->entityMapping = $this->parseMapping();
        $this->entityClass     = current(array_keys($this->entityMapping));
        $this->classMapping    = &$this->entityMapping[$this->entityClass];
        $this->classInflector  = new ClassInflector($this->entityClass);

        if (isset($this->classMapping['repositoryClass'])) {
            $this->repositoryClass = $this->classMapping['repositoryClass'];
            $this->repositoryInflector = new ClassInflector($this->repositoryClass);
        }
    }

    /**
     * @return array
     */
    public function parseMapping()
    {
        if (!$this->ymlParser) {
            $this->ymlParser = new YmlParser();
        }

        $this->originalMapping = $this->ymlParser->parse($this->content);
        $this->entityMapping   = $this->originalMapping;
    }

    /**
     * @param array $inflectedFields
     * @param int $mode
     * @return mixed
     */
    public function inflect($inflectedFields = array(), $mode = self::MODE_CAMELIZE)
    {
        $inflectedFields = $inflectedFields ?: $this->parseInflectedFields($mode);
        $this->inflectFields($mode, true, array_keys($inflectedFields));
        $this->classInflector->inflect($inflectedFields, $mode);
        $this->content = $this->ymlParser->dump($this->entityMapping, 6, 4, false, true);

        // Inflect repository
        if ($this->repositoryInflector) {
            $this->repositoryInflector->inflect($inflectedFields, $mode);
        }

        return $inflectedFields;
    }

    /**
     * @param int $mode
     * @return array
     */
    public function parseInflectedFields($mode = self::MODE_CAMELIZE)
    {
        $inflectedFields = $this->inflectFields($mode, false);
        $inflectedFields = array_merge($inflectedFields, $this->classInflector->parseInflectedProperties($mode));

        return $inflectedFields;
    }

    /**
     * @param $mode
     * @param bool $changeMapping
     * @param array $allowedChangeFields
     * @return array
     */
    protected function inflectFields($mode, $changeMapping = false, $allowedChangeFields = array())
    {
        $inflectedFields = array();

        // Inflect fields
        if (isset($this->classMapping['fields'])) {
            foreach (array_keys($this->classMapping['fields']) as $field) {
                if ($allowedChangeFields && !in_array($field, $allowedChangeFields)) continue;
                $inflectedFields = array_merge($inflectedFields, $this->inflectField($field, $mode, $changeMapping));
            }
        }

        // Inflect relationships
        foreach (array('oneToOne', 'oneToMany', 'manyToMany', 'manyToOne') as $relationType) {
            if (isset($this->classMapping[$relationType])) {
                $inflectedFields = array_merge($inflectedFields, $this->inflectRelation($relationType, $mode, $changeMapping, $allowedChangeFields));
            }
        }

        return $inflectedFields;
    }

    /**
     * @param $field
     * @param int $mode
     * @param bool $changeMapping
     * @return array
     */
    public function inflectField($field, $mode = self::MODE_CAMELIZE, $changeMapping = true)
    {
        $modifiedField = array();

        if (!isset($this->classMapping['fields'][$field])) {
            throw new \InvalidArgumentException(
                sprintf('Field %s not exists in this mapping', $field)
            );
        }

        $fieldMapping = $this->classMapping['fields'][$field];
        $newField = $this->inflectString($field, $mode);

        if ($newField != $field) {
            $modifiedField[$field] = $newField;
            if ($changeMapping) {
                unset($this->classMapping['fields'][$field]);
                $field = $newField;
            }
        }

        if ($changeMapping) {
            if (!isset($fieldMapping['column'])) {
                $fieldMapping['column'] = $this->inflectString($field, self::MODE_TABLEIZE);
            }
            $this->classMapping['fields'][$field] = $fieldMapping;
            $this->changedFields = array_merge($this->changedFields, $modifiedField);
        }

        return $modifiedField;
    }

    /**
     * @param $relationType
     * @param int $mode
     * @param bool $modifyMapping
     * @param array $allowedChangeFields
     *
     * @return array
     */
    public function inflectRelation($relationType, $mode = self::MODE_CAMELIZE, $modifyMapping = true, $allowedChangeFields = array())
    {
        $changedFields = array();

        if (!isset($this->classMapping[$relationType])) {
            throw new \InvalidArgumentException(
                sprintf('Relation %s not exists in this mapping', $relationType)
            );
        }
        $relationMapping = &$this->classMapping[$relationType];

        foreach ($relationMapping as $relationName => $config) {
            if ($allowedChangeFields && !in_array($relationName, $allowedChangeFields)) continue;

            $newName = $this->inflectString($relationName, $mode);

            if ($newName != $relationName) {
                $changedFields[$relationName] = $newName;
                if ($modifyMapping) {
                    unset($relationMapping[$relationName]);
                    $relationName = $newName;
                }
            }
            if ($modifyMapping) {
                foreach (array('mappedBy', 'inversedBy', 'indexedBy', 'orderBy') as $mappedAttribute) {
                    if (isset($config[$mappedAttribute])) {
                        $config[$mappedAttribute] = $this->inflectString($config[$mappedAttribute], $mode);
                    }
                }
                $relationMapping[$relationName] = $config;
            }
        }

        if ($modifyMapping) {
            $this->changedFields = array_merge($this->changedFields, $changedFields);
        }

        return $changedFields;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param bool $inflected
     * @return array
     */
    public function getEntityMapping($inflected = false)
    {
        return $inflected ? $this->entityMapping : $this->originalMapping;
    }

    /**
     * @return mixed
     */
    public function getClassMapping()
    {
        return $this->classMapping;
    }

    /**
     * @return mixed
     */
    public function getOriginalMapping()
    {
        return $this->originalMapping;
    }

    /**
     * @return mixed
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @return mixed
     */
    public function getRepositoryClass()
    {
        return $this->repositoryClass;
    }

    /**
     * @return ClassInflector
     */
    public function getClassInflector()
    {
        return $this->classInflector;
    }

    /**
     * @return ClassInflector
     */
    public function getRepositoryInflector()
    {
        return $this->repositoryInflector;
    }

    /**
     * @return array
     */
    public function getChangedFields()
    {
        return $this->changedFields;
    }

    /**
     * @param int $mode
     * @return void
     */
    public function save($mode = self::SAVE_MODE_PREVIEW)
    {
        parent::save($mode);
        $this->classInflector->save($mode);
    }

    /**
     * @return void
     */
    public function restore()
    {
        $this->classInflector->restore();
        parent::restore(false);
        $this->loadEntity();
    }
}