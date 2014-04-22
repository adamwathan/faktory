<?php

use Illuminate\Database\Capsule\Manager as DB;

abstract class FunctionalTestCase extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->configureDatabase();
    }

    protected function configureDatabase()
    {
        $db = new DB;
        $db->addConnection([
            'driver'    => 'sqlite',
            'host'      => 'localhost',
            'database'  => ':memory:',
            'username'  => 'root',
            'password'  => 'root',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            ]);
        $db->bootEloquent();
        $db->setAsGlobal();
    }
}
