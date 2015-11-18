<?php

namespace ClickLab\Inflector;

/**
 * @property string content
 * @property string $viewFile
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */
class ViewInflectorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->viewFile = __DIR__ . '/_files/mock.html.twig';
        $this->content = file_get_contents($this->viewFile);
    }

    public function testExtractObjAttrs()
    {
        $expectedVars = array(
            'object.other_attribute_with_filter | custom_filter | other_filter', 'form_widget(form.form_field)',
            'form_error(form.form_field)', 'object.camel_attribute', 'object.attribute'
        );
        $expectedObjs = array('object.other_attribute_with_filter', 'object.camel_attribute', 'object.attribute');
        $this->assertEquals($expectedVars, ViewInflector::extractViewObjAttrs($this->content, false));
        $this->assertEquals($expectedObjs, ViewInflector::extractViewObjAttrs($this->content, true));
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

    public function testLoadFile()
    {
        $viewInflector = new ViewInflector(null, $this->viewFile);
        $this->assertEquals($this->content, $viewInflector->getContent());
        $this->assertEquals($this->viewFile, $viewInflector->getFile());
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

    public function testRestore()
    {
        $viewInflector = new ViewInflector(null, $this->viewFile);
        $viewInflector->inflect();
        $viewInflector->restore();
    }
}
