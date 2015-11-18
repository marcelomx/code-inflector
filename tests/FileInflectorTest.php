<?php

namespace ClickLab\Inflector;

/**
 * @property string file
 * @property string content
 * @property string tmpFile
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */
class FileInflectorTest extends \PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $this->file =__DIR__ . '/_files/mock.html.twig';
        $this->content = file_get_contents($this->file);
        $this->tmpFile = sys_get_temp_dir() . '/' . uniqid();
        file_put_contents($this->tmpFile, $this->content);
    }

    function tearDown()
    {
        @unlink($this->tmpFile);
    }

    function testLoadFile()
    {
        $this->assertEquals(file_get_contents($this->file), FileInflector::loadFile($this->file));
    }

    function testInvalidFile()
    {
        $this->setExpectedException('\RuntimeException');
        FileInflector::loadFile('__INVALID_FILE__');
    }

    function testLoadContent()
    {
        $inflector = new FileInflector($this->file);
        $this->assertEquals($this->content, $inflector->getContent());
        $this->assertEquals($this->content, $inflector->getContent(true));
        $this->assertEquals($this->file, $inflector->getFile());
    }

    function testInflect()
    {
        $inflector = new FileInflector($this->file);
        $variables = array('object.camel_attribute');
        $inflector->inflect($variables);
        $this->assertNotEquals($this->content, $inflector->getContent());
        $this->assertEquals($this->content, $inflector->getContent(true));
    }

    function testSaveFile()
    {
        $inflector = new FileInflector($this->tmpFile);
        $inflector->save(FileInflector::SAVE_MODE_PREVIEW);
        $this->assertFileExists($this->tmpFile . '.preview~');
        $inflector->save(FileInflector::SAVE_MODE_BACKUP);
        $this->assertFileExists($this->tmpFile, '.backup~');
        $variables = array('object.camel_attribute');
        $inflector->inflect($variables);
        $inflector->save(FileInflector::SAVE_MODE_OVERWRITE);
        $this->assertEquals($inflector->getContent(), file_get_contents($this->tmpFile));
    }

    function testRestoreFile()
    {
        $inflector = new FileInflector($this->tmpFile);
        $variables = array('object.camel_attribute');
        $inflector->inflect($variables);
        $inflector->save(FileInflector::SAVE_MODE_BACKUP);
        $this->assertFileExists($this->tmpFile, '.backup~');
        $inflector->restore();
        $this->assertEquals($inflector->getContent(), file_get_contents($this->tmpFile));
    }
}
