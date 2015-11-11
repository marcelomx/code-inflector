<?php

/**
 * @property string content
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */
class ViewInflectorTest extends PHPUnit_Framework_TestCase 
{
    public function setUp()
    {
        $this->content = file_get_contents(__DIR__ . '/_files/mock.html.twig');
    }

    public function testExtractObjAttrs()
    {
        //$expectedAttrs = array('object.attribute', 'object.other_attr', 'object.attribute_finish');
        ///$this->assertEquals($expectedAttrs, ViewInflector::extractViewObjAttrs($this->content));
    }

    public function testExtratVariables()
    {
        $expectedVars = array (
            'other_attribute_with_filter',  'helper_object_value', 'helper_attr_value', 'camel_attribute',
             'with_atttribute', 'form_field', 'attribute', 'object');

        $expectedObjs = array(
            'object.other_attribute_with_filter', 'object.helper_object_value', 'object.with_atttribute',
            'object.camel_attribute', 'helper_attr_value', 'object.attribute', 'form_field');

        $this->assertEquals($expectedVars, ViewInflector::extractViewVariables($this->content, false));
        $this->assertEquals($expectedObjs, ViewInflector::extractViewVariables($this->content, true));
    }

    public function testParseVariables()
    {
        $viewInflector = new ViewInflector($this->content);
        $variables = $viewInflector->parseVariables();
        $this->assertEquals($variables, ViewInflector::extractViewVariables($this->content, false));
    }

    public function testInflectedVariables()
    {
        $viewInflector = new ViewInflector($this->content);
        $variables = $viewInflector->getVariables();
        $this->assertInternalType('array', $variables);
        $this->assertGreaterThanOrEqual(1, count($variables));
        $inflectedVariables = $viewInflector->getInflectedVariables();
        $this->assertEquals(ViewInflector::inflectArray($variables), $inflectedVariables);
        $viewInflector->setParseVariableAsObjs(true);
        $this->assertNotEquals($variables, $viewInflector->getVariables());
    }

    public function testInflect()
    {
        $expectedOutput = file_get_contents(__DIR__ . '/_output/mock.html.twig');
        $viewInflector = new ViewInflector($this->content);
        $viewInflector->inflect();
        $this->assertEquals($expectedOutput, $viewInflector->getContent());
        $this->assertNotEquals($expectedOutput, $viewInflector->getContent(true));
        $this->assertEquals($this->content, $viewInflector->getContent(true));
    }

    public function testInflectWithCustomVariables()
    {
        $content = $this->content . '{{ custom_variable }}';
        $expectedOutput = file_get_contents(__DIR__ . '/_output/mock.html.twig');
        $expectedOutput .= '{{ custom_variable }}';

        $viewInflector = new ViewInflector($content);
        $viewInflector->inflect();
        $variables = $viewInflector->getVariables();
        if (false !== ($key = array_search('custom_variable', $variables))) unset($variables[$key]);
        $viewInflector->inflect($variables);
        $this->assertEquals($expectedOutput, $viewInflector->getContent());
    }
}
