<?php

use Vehikl\Facktory\Relationship\Relationship as AbstractRelationship;
use Mockery as M;

class RelationshipTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        M::close();
    }

    public function test_can_guess_foreign_key_with_basic_class()
    {
        $factory = M::mock('Vehikl\\Facktory\\Factory');
        $relationship = new Relationship('Post', $factory);

        $expected = 'post_id';
        $this->assertSame($expected, $relationship->getForeignKey());
    }

    public function test_can_guess_foreign_key_with_namespaced_class()
    {
        $factory = M::mock('Vehikl\\Facktory\\Factory');
        $relationship = new Relationship('Foo\\Bar\\Post', $factory);

        $expected = 'post_id';
        $this->assertSame($expected, $relationship->getForeignKey());
    }

    public function test_specified_foreign_key_takes_precedence()
    {
        $factory = M::mock('Vehikl\\Facktory\\Factory');
        $relationship = new Relationship('Foo\\Bar\\Post', $factory);

        $relationship->foreignKey('post');
        $expected = 'post';
        $this->assertSame($expected, $relationship->getForeignKey());
    }

    public function test_specified_foreign_key_in_constructor_takes_precedence()
    {
        $factory = M::mock('Vehikl\\Facktory\\Factory');
        $relationship = new Relationship('Foo\\Bar\\Post', $factory, 'post');

        $expected = 'post';
        $this->assertSame($expected, $relationship->getForeignKey());
    }
}

class Relationship extends AbstractRelationship
{
    public function build() {}
}
