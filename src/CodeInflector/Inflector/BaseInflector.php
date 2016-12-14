<?php

namespace CodeInflector\Inflector;

use Doctrine\Common\Inflector\Inflector;

/**
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */ 
class BaseInflector 
{
    const MODE_CAMELIZE  = 1;
    const MODE_TABLEIZE  = 2;
    const MODE_CLASSIFY  = 3;
    const MODE_DASHERIZE = 4;

    /**
     * @var array
     */
    protected static $excludeVariables = array(
        'block', 'body', 'do', 'embed', 'expression', 'flush', 'for', 'forloop', 'if', 'elseif',
        'import', 'include', 'macro', 'print', 'sandbox', 'set', 'settemp', 'spaceless', 'text',
        'endif', 'endfor', 'endwhile', 'endblock', 'end', 'form', 'form_widget', 'form_error'
    );

    /**
     * @param array $variables
     * @param bool $sort
     * @return array
     */
    public static function filterVariables($variables = array(), $sort = true)
    {
        $variables = array_unique($variables);
        $variables = array_filter($variables,
            function($variable) {
                return static::filterVariable($variable) ?: false;
            });

        if ($sort) {
            usort($variables,
                function($a, $b) {
                    $sa = strlen($a); $sb = strlen($b);
                    return $sa == $sb ? 0 : ($sa < $sb ? 1 : -1);
                });
        }

        return array_values($variables);
    }

    /**
     * @param string $variable
     * @return string
     */
    public static function filterVariable($variable)
    {
        return !in_array($variable, static::$excludeVariables) ? trim($variable) : null;
    }

    /**
     * @param $string
     * @param int $mode
     * @return string
     */
    public static function inflectString($string, $mode = self::MODE_CAMELIZE)
    {
        $tokens = explode(".", $string);
        $tokens = array_map(function($string) use ($mode)
        {
            if (self::MODE_CAMELIZE == $mode) $string = Inflector::camelize($string);
            if (self::MODE_TABLEIZE == $mode) $string = Inflector::tableize($string);
            if (self::MODE_CLASSIFY == $mode) $string = Inflector::classify($string);

            return $string;
        }, $tokens);

        return implode(".", $tokens);
    }

    /**
     * @param array $array
     * @param int $mode
     * @return array
     */
    public static function inflectArray(array $array, $mode = self::MODE_CAMELIZE)
    {
        foreach ($array as $key => $value)
            $array[$key] = is_array($value)
                ? static::inflectArray($value, $mode)
                : static::inflectString($value, $mode);

        return $array;
    }

    /**
     * @param string $content
     * @param array $values
     * @param int $mode
     * @return string
     */
    public static function inflectContent($content, array $values = array(), $mode = self::MODE_CAMELIZE)
    {
        $values = array_map('preg_quote', $values);
        $pattern = sprintf("(%s)", implode("|", $values));

        return preg_replace_callback(
            $pattern,
            function($match) use ($mode) {
                return static::inflectString($match[0], $mode);
            },
            $content);
    }
}