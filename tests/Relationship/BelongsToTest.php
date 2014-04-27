<?php

use AdamWathan\Facktory\Relationship\BelongsTo;
use Mockery as M;

class BelongsToTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        M::close();
    }

    public function test_can_guess_foreign_key_with_basic_class()
    {
        $factory = M::mock('AdamWathan\\Facktory\\Factory');
        $factory->shouldReceive('getModel')->andReturn('Post');

        $relationship = new BelongsTo('Comment', $factory);
        $expected = 'post_id';
        $this->assertSame($expected, $relationship->getForeignKey());
    }

    public function test_can_guess_foreign_key_with_namespaced_class()
    {
        $factory = M::mock('AdamWathan\\Facktory\\Factory');
        $factory->shouldReceive('getModel')->andReturn('Foo\\Bar\\Post');

        $relationship = new BelongsTo('Comment', $factory);
        $expected = 'post_id';
        $this->assertSame($expected, $relationship->getForeignKey());
    }

    public function test_specified_foreign_key_takes_precedence()
    {
        $factory = M::mock('AdamWathan\\Facktory\\Factory');
        $relationship = new BelongsTo('Comment', $factory);

        $relationship->foreignKey('parent_post');
        $expected = 'parent_post';
        $this->assertSame($expected, $relationship->getForeignKey());
    }
}
