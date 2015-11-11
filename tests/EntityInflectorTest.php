<?php

require_once __DIR__ . '/_files/MockEntity.php';
require_once __DIR__ . '/_files/MockEntityRepository.php';

/**
 * @property string entityFile
 * @property EntityInflector inflector
 * @property \Symfony\Component\Yaml\Yaml yamlParser
 * @property string outputEntity
 * @property array outputYaml
 * @property array inputYaml
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */
class EntityInflectorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->entityFile = __DIR__ . '/_files/MockEntity.orm.yml';
        $this->inflector = new EntityInflector($this->entityFile);

        $this->yamlParser = new \Symfony\Component\Yaml\Yaml();
        $this->inputYaml = $this->yamlParser->parse(file_get_contents($this->entityFile));
        $this->outputYaml = $this->yamlParser->parse(file_get_contents(__DIR__ . '/_output/MockEntity.orm.yml'));
        $this->outputEntity = file_get_contents(__DIR__ . '/_output/MockEntity.php');
    }

    public function testLoadClass()
    {
        $classAttrs = array (
            0 => 'test_field',
            1 => 'one_to_many_entity',
            2 => 'many_to_one_entity',
            3 => 'many_to_many_entity',
            4 => 'not_mapped_attribute',
            5 => 'inversed_field',
            6 => 'inversed_one_field',
            7 => 'inversed_many_field',
        );

        $this->assertEquals($classAttrs, $this->inflector->getClassAttrs());
        $this->assertEquals($this->entityFile, $this->inflector->getEntityFile());
        $this->assertEquals(__DIR__ . '/_files/MockEntityRepository.php', $this->inflector->getRepositoryFile());
        $this->assertEquals($this->inputYaml, $this->inflector->getEntityMapping());
    }

    public function testInflectField()
    {
        $this->inflector->inflectField('test_field');
        $classMapping = $this->inflector->getClassMapping();
        $this->assertFalse(isset($classMapping['fields']['test_field']));
        $this->assertTrue(isset($classMapping['fields']['testField']));
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

    public function testInflectInvalidRelation()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->inflector->inflectRelation('oneToVary');
    }

    public function testInflect()
    {
        $this->inflector->inflect();
        $this->assertEquals($this->outputYaml, $this->inflector->getEntityMapping(true));
        $this->assertEquals($this->outputEntity, $this->inflector->getClassContent());
    }

    function testInflectWithCustomModifiedAttributes()
    {
        $classAttrs = array (
            0 => 'test_field',
            1 => 'one_to_many_entity',
            2 => 'many_to_one_entity',
            3 => 'many_to_many_entity',
            4 => 'not_mapped_attribute',
            5 => 'inversed_field',
            6 => 'inversed_one_field',
            7 => 'inversed_many_field',
        );
        $customAttrs = array();
        foreach ($classAttrs as $attr) {
            $customAttrs[$attr] = ClassInflector::inflectString($attr);
        }
        unset($customAttrs['inversed_many_field']);
        $expectedOutput = preg_replace('/inversedManyField/', 'inversed_many_field', $this->outputEntity);
        $this->inflector->inflect($customAttrs);
        $this->assertEquals($expectedOutput, $this->inflector->getClassContent());
    }
}
