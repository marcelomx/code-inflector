<?php

class EntityInflector
{
    const MODE_CAMELIZE = 1;
    const MODE_TABLEIZE = 2;

    protected $backupExtension  = '.backup~';
    protected $previewExtension = '.preview~';

    /** @var  YamlParser */
    protected $ymlParser;
    protected $entityFile;
    protected $entityMapping;
    protected $classMapping;
    protected $className;
    protected $classFile;
    protected $classContent;
    protected $classAttrs = array();
    protected $modifyAttributes = array();
    protected $repositoryFile;
    protected $repositoryContent;

    /**
     * @param string $entityFile
     * @param YamlParser $yamlParser
     */
    public function __construct($entityFile, $yamlParser = null)
    {
        $this->yamlParser = $yamlParser ?: new YamlParser();
        $this->entityFile = $entityFile;
        $this->loadClass();
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

        if (!class_exists($this->className)) {
            throw new \RuntimeException(
                sprintf('Entity class %s not exists', $this->className)
            );
        }
        $refClass = new \ReflectionClass($this->className);
        $this->classFile = $refClass->getFileName();

        if (file_exists($this->classFile)) {
            $this->classContent = file_get_contents($this->classFile);
        }

        // reset values
        $this->classAttrs = static::parseClassAttrs($refClass);
        $this->modifyAttributes = array();

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
     * @param \ReflectionClass|string $refClass
     * @return array
     */
    public static function parseClassAttrs($refClass)
    {
        if (!$refClass instanceof \ReflectionClass) {
            $refClass = new \ReflectionClass($refClass);
        }
        $classAttrs = array();

        foreach ($refClass->getProperties() as $property) {
            $classAttrs[] = $property->getName();
        }
        $content = file_get_contents($refClass->getFileName());
        preg_match_all("/\\\$this->([\w]+)([^\w\(])/", $content, $matches, PREG_SET_ORDER);

        if ($matches) {
            foreach ($matches as $match) {
                $classAttrs[] = $match[1];
            }
        }

        return array_unique($classAttrs);
    }

    /**
     * @param int $mode
     */
    public function inflect($mode = self::MODE_CAMELIZE)
    {
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

        // Inflect native attributes
        foreach ($this->classAttrs as $attr) {
            $newAttr = $this->inflectString($attr, $mode);
            if ($newAttr != $attr) $this->modifyAttributes[$attr] = $newAttr;
        }

        // Parse modified attributes and change class
        if ($this->modifyAttributes) {
            foreach ($this->modifyAttributes as $attr => $newAttr) {
                $this->classContent = preg_replace("/var (.*) \\$$attr([^\w])/", "var \\1 \\$$newAttr\\2", $this->classContent);
                $this->classContent = preg_replace("/(private|protected) \\$$attr([^\w])/", "\\1 \\$$newAttr\\2", $this->classContent);
                $this->classContent = preg_replace("/(Set|Get|Add|Remove) $attr([^\w])/", "\\1 $newAttr\\2", $this->classContent);
                $this->classContent = preg_replace("/\\\$this->$attr([^\w])/", "\\\$this->$newAttr$1", $this->classContent);
                // Replace references in repository
                //$this->repositoryContent = preg_replace("/$attr/", "$newAttr", $this->repositoryContent);
            }
        }

        return $this->classMapping;
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

            foreach (array('mappedBy', 'inversedBy') as $mappedAttribute) {
                if (isset($config[$mappedAttribute])) {
                    $config[$mappedAttribute] = $this->inflectString($config[$mappedAttribute], $mode);
                }
            }
            $relationMapping[$relationName] = $config;
        }

        $this->classMapping[$relationType] = $relationMapping;
    }

    /**
     * @param $string
     * @param int $mode
     * @return string
     */
    protected function inflectString($string, $mode = self::MODE_CAMELIZE)
    {
        if (self::MODE_CAMELIZE == $mode) $string = Inflector::camelize($string);
        if (self::MODE_TABLEIZE == $mode) $string =  Inflector::tableize($string);

        return $string;
    }

    /**
     * @return YamlParser
     */
    public function getYmlParser()
    {
        return $this->ymlParser;
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
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return array
     */
    public function getClassAttrs()
    {
        return $this->classAttrs;
    }

    /**
     * @return array
     */
    public function getModifyAttributes()
    {
        return $this->modifyAttributes;
    }

    /**
     * @return mixed
     */
    public function getClassFile()
    {
        return $this->classFile;
    }

    /**
     * @return mixed
     */
    public function getClassContent()
    {
        return $this->classContent;
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