<?php

require_once __DIR__ . '/_files/MockEntity.php';

/**
 * @property ClassInflector inflector
 * @property string outputEntity
 * @property string inputEntity
 * @property array classAttrs
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */
class ClassInflectorTest extends PHPUnit_Framework_TestCase 
{
    function setUp()
    {
        $this->inflector = new ClassInflector('MockEntity');
        $this->inputEntity = file_get_contents(__DIR__ . '/_files/MockEntity.php');
        $this->outputEntity = file_get_contents(__DIR__ . '/_output/MockEntity.php');
        $this->classAttrs = array (
            0 => 'test_field',
            1 => 'one_to_many_entity',
            2 => 'many_to_one_entity',
            3 => 'many_to_many_entity',
            4 => 'not_mapped_attribute',
            5 => 'inversed_field',
            6 => 'inversed_one_field',
            7 => 'inversed_many_field',
        );
    }

    function testParseClassAttrs()
    {
        $this->assertEquals($this->classAttrs, ClassInflector::parseClassAttrs('MockEntity'));
    }

    function testLoadClass()
    {
        $this->assertEquals($this->inflector->getClassName(), 'MockEntity');
        $this->assertEquals($this->inflector->getClassContent(), $this->inputEntity);
        $this->assertEquals($this->inflector->getClassFile(), __DIR__ . '/_files/MockEntity.php');
        $this->assertEquals($this->inflector->getModifyAttributes(), array());
        $this->assertEquals($this->classAttrs, $this->inflector->getClassAttrs());
    }

    function inflectContent()
    {
        $this->inflector->inflect();
        $this->assertNotEquals($this->classAttrs, $this->inflector->getModifyAttributes());
        $this->assertEquals($this->outputEntity, $this->inflector->getClassContent());
    }

    function testInflectWithCustomModifiedAttributes()
    {
        $customAttrs = array();
        foreach ($this->classAttrs as $attr) {
            $customAttrs[$attr] = ClassInflector::inflectString($attr);
        }
        unset($customAttrs['inversed_many_field']);
        $expectedOutput = preg_replace('/inversedManyField/', 'inversed_many_field', $this->outputEntity);
        $this->inflector->inflect($customAttrs);
        $this->assertEquals($expectedOutput, $this->inflector->getClassContent());
    }
}
