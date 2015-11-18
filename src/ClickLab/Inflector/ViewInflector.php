<?php

namespace ClickLab\Inflector;

/**
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */ 
class ViewInflector extends FileInflector
{
    const REGEX_TEMPLATE_VARIABLES = "/\{\{([^\{\}]+)\}\}/";
    const REGEX_OBJECT_VARIABLE    = "/^([\w\_\.]+)/";

    protected $originalContent;
    protected $content;
    protected $file;
    protected $variables = array();
    protected $parseObjects = false;

    /**
     * @param string $content
     * @param null $viewFile
     */
    public function __construct($content = null, $viewFile = null)
    {
        $this->originalContent = $this->content = $content;
        $this->file = $viewFile;

        $this->loadView();
    }

    /**
     * @return string
     */
    public function loadView()
    {
        if (!$this->content && $this->file) {
            parent::loadContent();
        }

        $this->parseVariables();
    }

    /**
     * @param string $content
     * @param bool $parseObjects
     * @return array
     */
    public static function extractViewObjAttrs($content, $parseObjects = true)
    {
        $objAttrs = array();

        preg_match_all(static::REGEX_TEMPLATE_VARIABLES, $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if ($parseObjects) {
                if (preg_match(static::REGEX_OBJECT_VARIABLE, trim($match[1]), $smatch)) {
                    $objAttrs[] = $smatch[1];
                }
            } else {
                $objAttrs[] = trim($match[1]);
            }
        }

        return static::filterVariables($objAttrs);
    }

    /**
     * @param string $content
     * @param bool $parseObjects
     * @return array
     */
    public static function extractViewVariables($content, $parseObjects = false)
    {
        // From: http://stackoverflow.com/questions/14281941/array-of-required-twig-variables-in-symfony

        $lexer = new \Twig_Lexer(new \Twig_Environment());
        $stream = $lexer->tokenize($content);
        $variables = array();
        $tokens = array();

        $appendVars = function($vars, $asObject) use (&$variables) {
            $vars = array_filter($vars, 'trim');
            if (count($vars)) {
                if ($asObject) $variables[] = implode(".", $vars);
                else $variables = array_merge($variables, $vars);
            }
        };

        while (!$stream->isEOF()) {
            $token = $stream->next();
            $tokens[] = $token;

            if ($token->getType() === \Twig_Token::NAME_TYPE) {
                $value = [$token->getValue()];
                $isFilterable = true;
                $asObject = false;

                while (!$stream->isEOF() && !in_array($token->getType(), [\Twig_Token::VAR_END_TYPE, \Twig_Token::BLOCK_END_TYPE])) {
                    $token = $stream->next();
                    $tokens[] = $token;

                    if ($token->getType() == \Twig_Token::PUNCTUATION_TYPE) {
                        if ('.' == $token->getValue() && $parseObjects) $asObject = true;
                        if ('|' == $token->getValue()) {
                            $isFilterable = false;
                            continue;
                        }
                        if ('(' == $token->getValue()) {
                            $value=[];
                            continue;
                        }
                    }

                    if (!$isFilterable) continue;

                    if ($token->getType() != \Twig_Token::NAME_TYPE) {
                        if ('.' !== $token->getValue()) {
                            $appendVars($value, $asObject);
                            $value = []; $asObject = false;
                        }
                    } else {
                        $value[] = static::filterVariable($token->getValue());
                    }
                }

                if ($value) {
                    $appendVars($value, $asObject);
                }
            }
        }

        return static::filterVariables($variables);
    }


    /**
     * @param bool $parseObjects
     */
    public function setParseVariableAsObjs($parseObjects = true)
    {
        $this->parseObjects = $parseObjects;
        $this->parseVariables();
    }

    /**
     * @return array
     */
    public function parseVariables()
    {
        $this->variables = static::extractViewVariables($this->content, $this->parseObjects);
        return $this->variables;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param int $mode
     * @return array
     */
    public function getInflectedVariables($mode = self::MODE_CAMELIZE)
    {
        $inflectedVariables = array();

        foreach ($this->getVariables() as $var) {
            $inflectedVar = static::inflectString($var, $mode);
            if ($inflectedVar != $var) $inflectedVariables[$var] = $inflectedVar;
        }

        return $inflectedVariables;
    }

    /**
     * @param array $variables
     * @param int $mode
     * @return string
     */
    public function inflect($variables = array(), $mode = self::MODE_CAMELIZE)
    {
        $variables = $variables ?: $this->getVariables();
        $this->content = static::inflectContent($this->originalContent, $variables);

        return $this->content;
    }

    /**
     * @return void
     */
    public function restore()
    {
        parent::restore(false);
        $this->loadView();
    }
}