<?php

namespace CodeInflector\Inflector;

require_once __DIR__ . '/_files/MockEntity.php';

/**
 * @property ClassInflector inflector
 * @property string outputEntity
 * @property string inputEntity
 * @property array classAttrs
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */
class ClassInflectorTest extends \PHPUnit_Framework_TestCase
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
        $this->assertEquals($this->classAttrs, ClassInflector::parseClassProperties('MockEntity'));
    }

    function testLoadClass()
    {
        $this->assertEquals($this->inflector->getClassName(), 'MockEntity');
        $this->assertEquals($this->inflector->getClassContent(), $this->inputEntity);
        $this->assertEquals($this->inflector->getContent(), $this->inputEntity);
        $this->assertEquals($this->inflector->getClassFile(), __DIR__ . '/_files/MockEntity.php');
        $this->assertEquals($this->inflector->getInflectedProperties(), array());
        $this->assertEquals($this->classAttrs, $this->inflector->getClassProperties());
    }

    function testLoadInvalidClass()
    {
        $this->setExpectedException('\RuntimeException');
        new ClassInflector('__Invalid_Class_' . time());
    }

    function testGetInflectedProperties()
    {
        $expectedProperties = array();
        foreach ($this->classAttrs as $attr) {
            $expectedProperties[$attr] = ClassInflector::inflectString($attr);
        }
        $inflectedProperties = $this->inflector->parseInflectedProperties();
        $this->assertInternalType('array', $inflectedProperties);
    }

    function testInflectContent()
    {
        $this->inflector->inflect();
        $this->assertNotEquals($this->classAttrs, $this->inflector->getInflectedProperties());
        $this->assertEquals($this->outputEntity, $this->inflector->getClassContent());
        $this->assertEquals($this->outputEntity, $this->inflector->getContent());
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

    function testRestore()
    {
        // coverage
        $this->inflector->restore();
    }
}
