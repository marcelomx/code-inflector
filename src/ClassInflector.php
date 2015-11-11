<?php

/**
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */ 
class ClassInflector extends BaseInflector
{
    protected $className;
    protected $classFile;
    protected $classContent;
    protected $classAttrs = array();
    protected $modifyAttributes = array();

    /**
     * @param null $className
     */
    public function __construct($className)
    {
        parent::__construct();

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
     * @param array $modifyAttrs
     * @param int $mode
     * @return mixed
     */
    public function inflect($modifyAttrs = array(), $mode = self::MODE_CAMELIZE)
    {
        if (!$modifyAttrs) {
            // Inflect native attributes
            foreach ($this->classAttrs as $attr) {
                $newAttr = static::inflectString($attr, $mode);
                if ($newAttr != $attr) $this->modifyAttributes[$attr] = $newAttr;
            }
            $modifyAttrs = $this->modifyAttributes;
        }

        // Parse modified attributes and change class
        if ($modifyAttrs) {
            foreach ($modifyAttrs as $attr => $newAttr) {
                $this->classContent = preg_replace("/var (.*) \\$$attr([^\w])/", "var \\1 \\$$newAttr\\2", $this->classContent);
                $this->classContent = preg_replace("/(private|protected) \\$$attr([^\w])/", "\\1 \\$$newAttr\\2", $this->classContent);
                $this->classContent = preg_replace("/(Set|Get|Add|Remove) $attr([^\w])/", "\\1 $newAttr\\2", $this->classContent);
                $this->classContent = preg_replace("/\\\$this->$attr([^\w])/", "\\\$this->$newAttr$1", $this->classContent);
            }
        }

        return $this->classContent;
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
}