<?php

namespace ClickLab\Inflector;

/**
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */
class BaseInflectorTest extends \PHPUnit_Framework_TestCase
{
    function testInflectString()
    {
        $this->assertEquals('stringCamel', BaseInflector::inflectString('string_camel'));
        $this->assertEquals('string_under', BaseInflector::inflectString('stringUnder', BaseInflector::MODE_TABLEIZE));
        $this->assertEquals('StringUnder', BaseInflector::inflectString('string under', BaseInflector::MODE_CLASSIFY));
    }

    function testInflectArray()
    {
        $stringUnder = array('string_camel', 'other_under');
        $stringCamel = array('stringCamel', 'otherUnder');

        $this->assertEquals($stringCamel, BaseInflector::inflectArray($stringUnder));
        $this->assertEquals([$stringCamel, $stringCamel], BaseInflector::inflectArray([$stringUnder, $stringUnder]));
    }

    function testFilterVariable()
    {
        $this->assertNull(BaseInflector::filterVariable('block'));
        $this->assertEquals('', BaseInflector::filterVariable(''));
        $this->assertEquals('test', BaseInflector::filterVariable('test '));
    }

    function testFilterVariables()
    {
        $variables = array('block', 'custom_variable', 'custom_sortable_variable');
        $this->assertEquals(['custom_sortable_variable', 'custom_variable'], BaseInflector::filterVariables($variables));
        $this->assertEquals(['custom_variable', 'custom_sortable_variable'], BaseInflector::filterVariables($variables, false));
    }

    function testInflectContent()
    {
        $content = 'content {{ custom_variable }} {{ no_inflected_variable }}';
        $this->assertEquals('content {{ customVariable }} {{ no_inflected_variable }}', BaseInflector::inflectContent($content, array('custom_variable')));
        $this->assertEquals('content {{ customVariable }} {{ noInflectedVariable }}', BaseInflector::inflectContent($content, array('custom_variable', 'no_inflected_variable')));
    }

    function testInflectContentSensitive()
    {
        $content = 'A custom_string and not Custom_String';
        $this->assertEquals('A customString and not Custom_String', BaseInflector::inflectContent($content, array('custom_string')));
        $this->assertEquals('A customString and not customString', BaseInflector::inflectContent($content, array('custom_string', 'Custom_String')));
    }
}
