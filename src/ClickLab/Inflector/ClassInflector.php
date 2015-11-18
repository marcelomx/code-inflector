<?php

namespace ClickLab\Inflector;

/**
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */ 
class ClassInflector extends FileInflector
{
    protected $className;
    protected $classFile;
    protected $classContent;
    protected $classProperties = array();
    protected $modifiedProperties = array();

    /**
     * @param null $className
     */
    public function __construct($className)
    {
        $this->className = $className;
        $this->loadClass();
    }

    /**
     * @return array
     */
    public function loadClass()
    {
        if (!class_exists($this->className)) {
            throw new \RuntimeException(
                sprintf('Class %s not exists', $this->className)
            );
        }
        $refClass = new \ReflectionClass($this->className);
        $this->file = $this->classFile = $refClass->getFileName();

        parent::loadContent();
        $this->classContent = &$this->content;

        // reset values
        $this->classProperties = static::parseClassAttrs($refClass);
        $this->modifiedProperties = array();
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
        $content = static::loadFile($refClass->getFileName());
        preg_match_all("/\\\$this->([\w]+)([^\w\(])/", $content, $matches, PREG_SET_ORDER);

        if ($matches) {
            foreach ($matches as $match) {
                $classAttrs[] = $match[1];
            }
        }

        return array_unique($classAttrs);
    }

    /**
     * @param array $inflectedProperties
     * @param int $mode
     * @return mixed
     */
    public function inflect($inflectedProperties = array(), $mode = self::MODE_CAMELIZE)
    {
        $modifiedProperties = $inflectedProperties ?: ($this->modifiedProperties = $this->getInflectedProperties($mode));

        // Parse modified attributes and change class
        if ($modifiedProperties) {
            foreach ($modifiedProperties as $attr => $newAttr) {
                $this->classContent = preg_replace("/var (.*) \\$$attr([^\w])/", "var \\1 \\$$newAttr\\2", $this->classContent);
                $this->classContent = preg_replace("/(private|protected) \\$$attr([^\w])/", "\\1 \\$$newAttr\\2", $this->classContent);
                $this->classContent = preg_replace("/(Set|Get|Add|Remove) $attr([^\w])/", "\\1 $newAttr\\2", $this->classContent);
                $this->classContent = preg_replace("/\\\$this->$attr([^\w])/", "\\\$this->$newAttr$1", $this->classContent);
            }
        }

        return $this->classContent;
    }

    /**
     * @param int $mode
     * @return array
     */
    public function getInflectedProperties($mode = self::MODE_CAMELIZE)
    {
        $inflectedProperties = array();

        // Inflect native attributes
        foreach ($this->classProperties as $prop) {
            $newProp = static::inflectString($prop, $mode);
            if ($newProp != $prop) $inflectedProperties[$prop] = $newProp;
        }

        return $inflectedProperties;
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
    public function getClassProperties()
    {
        return $this->classProperties;
    }

    /**
     * @return array
     */
    public function getModifiedProperties()
    {
        return $this->modifiedProperties;
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
     * @return void
     */
    public function restore()
    {
        parent::restore(false);
        $this->loadClass();
    }
}