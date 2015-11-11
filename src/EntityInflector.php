<?php

use Symfony\Component\Yaml\Yaml as YamlParser;

class EntityInflector extends ClassInflector
{
    /** @var  YamlParser */
    protected $ymlParser;
    protected $entityFile;
    protected $entityMapping;
    protected $classMapping;
    protected $repositoryFile;
    protected $repositoryContent;

    /**
     * @param string $entityFile
     */
    public function __construct($entityFile)
    {
        $this->entityFile = $entityFile;

        parent::__construct(null);
    }

    /**
     * @return void
     */
    public function loadClass()
    {
        $this->entityMapping = $this->yamlParser->parse(file_get_contents($this->entityFile));
        $keys = array_keys($this->entityMapping);
        $this->className = $keys[0];
        $this->classMapping = $this->entityMapping[$this->className];

        parent::loadClass();

        // parse repository content
        if (isset($this->classMapping['repositoryClass'])) {
            $refClass = new \ReflectionClass($this->classMapping['repositoryClass']);
            $this->repositoryFile = $refClass->getFileName();
            $this->repositoryContent = file_get_contents($this->repositoryFile);
        } else {
            $this->repositoryFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        }
    }

    /**
     * @param array $modifyAttrs
     * @param int $mode
     * @return mixed
     */
    public function inflect($modifyAttrs = array(), $mode = self::MODE_CAMELIZE)
    {
        if (!$modifyAttrs) {
            // Inflect fields
            if (isset($this->classMapping['fields'])) {
                foreach (array_keys($this->classMapping['fields']) as $field) {
                    $this->inflectField($field, $mode);
                }
            }

            // Inflect relationships
            foreach (array('oneToOne', 'oneToMany', 'manyToMany', 'manyToOne') as $relationType) {
                if (isset($this->classMapping[$relationType])) {
                    $this->inflectRelation($relationType, $mode);
                }
            }
        }

        return parent::inflect($modifyAttrs, $mode);
    }

    /**
     * @param bool $onlyPreview
     * @return void
     */
    public function save($onlyPreview = false)
    {
        $yml = $this->yamlParser->dump($this->getEntityMapping(true), 6, 4, false, true);

        $saveFiles = array(
            $this->entityFile => $yml,
            $this->classFile => $this->classContent
            //$this->repositoryFile => $this->repositoryContent
        );

        foreach ($saveFiles as $file => $content) {
            if ($onlyPreview) {
                $previewFile = $file . $this->previewExtension;
                file_put_contents($previewFile, $content);
            } else {
                $backupFile = $file . $this->backupExtension;
                copy($file, $backupFile);
                file_put_contents($file, $content);
            }
        }
    }

    /**
     * @return void
     */
    public function restore()
    {
        foreach (array($this->entityFile, $this->classFile) as $file) {
            $backupFile = $file . $this->backupExtension;
            $previewFile = $file . $this->previewExtension;
            if (file_exists($backupFile)) {
                copy($backupFile, $file);
            }
            @unlink($previewFile);
        }

        $this->loadClass(); // Reload configuration!
    }

    /**
     * @param $field
     * @param int $mode
     */
    public function inflectField($field, $mode = self::MODE_CAMELIZE)
    {
        if (!isset($this->classMapping['fields'][$field])) {
            throw new \InvalidArgumentException(
                sprintf('Field %s not exists in this mapping', $field)
            );
        }
        $fieldMapping = $this->classMapping['fields'][$field];
        $newField = $this->inflectString($field, $mode);

        if ($newField != $field) {
            unset($this->classMapping['fields'][$field]);
            $this->modifyAttributes[$field] = $newField;
            $field = $newField;
        }

        if (!isset($fieldMapping['column'])) {
            $fieldMapping['column'] = $this->inflectString($field, self::MODE_TABLEIZE);
        }

        $this->classMapping['fields'][$field] = $fieldMapping;
    }

    /**
     * @param $relationType
     * @param int $mode
     */
    public function inflectRelation($relationType, $mode = self::MODE_CAMELIZE)
    {
        if (!isset($this->classMapping[$relationType])) {
            throw new \InvalidArgumentException(
                sprintf('Relation %s not exists in this mapping', $relationType)
            );
        }
        $relationMapping = $this->classMapping[$relationType];

        foreach ($relationMapping as $relationName => $config) {
            $newName = $this->inflectString($relationName, $mode);

            if ($newName != $relationName) {
                $this->modifyAttributes[$relationName] = $newName;
                unset($relationMapping[$relationName]);
                $relationName = $newName;
            }

            foreach (array('mappedBy', 'inversedBy', 'indexedBy', 'orderBy') as $mappedAttribute) {
                if (isset($config[$mappedAttribute])) {
                    $config[$mappedAttribute] = $this->inflectString($config[$mappedAttribute], $mode);
                }
            }
            $relationMapping[$relationName] = $config;
        }

        $this->classMapping[$relationType] = $relationMapping;
    }

    /**
     * @return string
     */
    public function getEntityFile()
    {
        return $this->entityFile;
    }

    /**
     * @param bool $inflected
     * @return array
     */
    public function getEntityMapping($inflected = false)
    {
        return $inflected ? array($this->className => $this->classMapping) : $this->entityMapping;
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
    public function getRepositoryFile()
    {
        return $this->repositoryFile;
    }

    /**
     * @return mixed
     */
    public function getRepositoryContent()
    {
        return $this->repositoryContent;
    }
}