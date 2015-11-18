<?php

namespace ClickLab\Inflector;

use ClickLab\Inflector\Console\Command\ClassCommand;

use Mockery as m;

/**
 * @property m\MockInterface input
 * @property m\MockInterface output
 * @property m\MockInterface inflector
 * @property ClassCommand command
 * @author Marcelo Rodrigues <marcelo.mx@gmail.com>
 * @api
 */
class ClassCommandTest extends \PHPUnit_Framework_TestCase 
{
    function setUp()
    {
        $this->input = m::mock('Symfony\Component\Console\Input\InputInterface');
        $this->output = m::mock('Symfony\Component\Console\Output\OutputInterface');
        $this->inflector = m::mock('overload:ClickLab\Inflector\ClassInflector');
        $this->input->shouldReceive('getArgument')->with('class')->andReturn('MockEntity');
        $this->inflector->shouldReceive('getClassName')->andReturn('MockEntity');

        $this->command = new ClassCommand();
    }
    
    function tearDown()
    {
        m::close();
    }

    function testExecuteCommand()
    {
        $this->inflector->shouldReceive('getInflectedProperties')->andReturn(array('inflect_property' => 'inflectedProperty'));
        $this->output->shouldReceive('writeln')->times(2);
        $this->command->execute($this->input, $this->output);
    }
}
