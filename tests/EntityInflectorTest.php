<?php

namespace ClickLab\Inflector;

require_once __DIR__ . '/_files/MockEntity.php';
require_once __DIR__ . '/_files/MockEntityRepository.php';

use Mockery as m;
use Symfony\Component\Yaml\Yaml;

/**
 * @property string entityFile
 * @property EntityInflector inflector
 * @property string outputEntity
 * @property array outputYaml
 * @property array inputYaml
 * @property m\MockInterface|Yaml yamlParser
 * @property array entityFields
 * @property string tmpFile
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */
class EntityInflectorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->entityFile = __DIR__ . '/_files/MockEntity.orm.yml';
        $this->inflector = new EntityInflector($this->entityFile);

        $this->yamlParser = m::mock(Yaml::class);
        $this->inputYaml = include(__DIR__ . '/_files/MockEntity.orm.php');
        $this->outputYaml = include(__DIR__ . '/_output/MockEntity.orm.php');
        $this->outputEntity = file_get_contents(__DIR__ . '/_output/MockEntity.php');
        $this->yamlParser->shouldReceive('parse')->andReturn($this->inputYaml);
        $this->yamlParser->shouldReceive('dump')->andReturn($this->outputYaml);
        $this->entityFields = array(
            0 => 'test_field',
            1 => 'test_field2',
            2 => 'one_to_many_entity',
            3 => 'many_to_many_entity',
            4 => 'many_to_one_entity'
        );
    }

    public function tearDown()
    {
        m::close();
    }

    public function testLoadClass()
    {
        $this->assertEquals($this->entityFile, $this->inflector->getFile());
        $this->assertEquals($this->inputYaml, $this->inflector->getEntityMapping());
        $this->assertEquals($this->inputYaml, $this->inflector->getOriginalMapping());
        $this->assertEquals('MockEntity', $this->inflector->getEntityClass());
        $this->assertEquals('MockEntityRepository', $this->inflector->getRepositoryClass());
        $this->assertInstanceOf(ClassInflector::class, $this->inflector->getClassInflector());
        $this->assertInstanceOf(ClassInflector::class, $this->inflector->getRepositoryInflector());
    }

    public function testInflectField()
    {
        $this->inflector->inflectField('test_field');
        $classMapping = $this->inflector->getClassMapping();
        $this->assertFalse(isset($classMapping['fields']['test_field']));
        $this->assertTrue(isset($classMapping['fields']['testField']));
    }

    public function testInflectFieldNoModifying()
    {
        $this->inflector->inflectField('test_field', EntityInflector::MODE_CAMELIZE, false);
        $classMapping = $this->inflector->getClassMapping();
        $this->assertTrue(isset($classMapping['fields']['test_field']));
        $this->assertFalse(isset($classMapping['fields']['testField']));
    }

    public function testInflectInvalidField()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->inflector->inflectField('test_field_invalid');
    }

    public function testInflectRelation()
    {
        $this->inflector->inflectRelation('oneToMany');
        $classMapping = $this->inflector->getClassMapping();
        $this->assertFalse(isset($classMapping['oneToMany']['one_to_many_entity']));
        $this->assertTrue(isset($classMapping['oneToMany']['oneToManyEntity']));
    }

    public function testInflectionRelationNoModifying()
    {
        $this->inflector->inflectRelation('oneToMany', EntityInflector::MODE_CAMELIZE, false);
        $classMapping = $this->inflector->getClassMapping();
        $this->assertTrue(isset($classMapping['oneToMany']['one_to_many_entity']));
        $this->assertFalse(isset($classMapping['oneToMany']['oneToManyEntity']));
    }

    public function testInflectInvalidRelation()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->inflector->inflectRelation('oneToVary');
    }

    public function testGetInflectedFields()
    {
        $inflectedProperties = $this->inflector->getClassInflector()->parseInflectedProperties();
        $modifiedFields = $this->getModifiedFields();
        $expectedFields = array_merge($modifiedFields, $inflectedProperties);
        $inflectedFields = $this->inflector->parseInflectedFields();
        $this->assertInternalType('array', $inflectedFields);
        $this->assertEquals($expectedFields, $inflectedFields);
    }

    public function testInflect()
    {
        $inflectedProperties = $this->inflector->inflect();
        $this->assertEquals($this->outputYaml, $this->inflector->getEntityMapping(true));
        $this->assertNotEquals($this->outputYaml, $this->inflector->getOriginalMapping());
        $this->assertEquals($this->getModifiedFields(), $this->inflector->getChangedFields());
        $this->assertEquals($this->getModifiedPropertiess(), $inflectedProperties);
    }

    function testInflectWithCustomModifiedAttributes()
    {
        $customAttrs = $this->getModifiedPropertiess();
        unset($customAttrs['inversed_many_field']);
        $expectedOutput = preg_replace('/inversedManyField/', 'inversed_many_field', $this->outputEntity);
        $this->inflector->inflect($customAttrs);
        $this->assertEquals($expectedOutput, $this->inflector->getClassInflector()->getClassContent());
    }

    /**
     * @return array
     */
    protected function getModifiedFields()
    {
        $expectedFields = array();
        foreach ($this->entityFields as $field) {
            $expectedFields[$field] = EntityInflector::inflectString($field);
        }
        return $expectedFields;
    }

    /**
     * @return array
     */
    protected function getModifiedPropertiess()
    {
        $classAttrs = array(
            0 => 'test_field',
            1 => 'one_to_many_entity',
            2 => 'many_to_one_entity',
            3 => 'many_to_many_entity',
            4 => 'not_mapped_attribute',
            5 => 'inversed_field',
            6 => 'inversed_one_field',
            7 => 'inversed_many_field',
            8 => 'test_field2'
        );
        $customAttrs = array();
        foreach ($classAttrs as $attr) {
            $customAttrs[$attr] = ClassInflector::inflectString($attr);
        }
        return $customAttrs;
    }

    public function testSaveRestore()
    {
        // to coverage
        $inflector = new EntityInflector($this->entityFile);
        $inflector->save(-1);
        $inflector->restore();
    }
}