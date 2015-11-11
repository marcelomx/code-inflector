<?php

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
    }

    function testInflectContent()
    {
        $content = 'content {{ custom_variable }} {{ no_inflected_variable }}';
        $this->assertEquals('content {{ customVariable }} {{ no_inflected_variable }}', BaseInflector::inflectContent($content, array('custom_variable')));
        $this->assertEquals('content {{ customVariable }} {{ noInflectedVariable }}', BaseInflector::inflectContent($content, array('custom_variable', 'no_inflected_variable')));
    }
}
