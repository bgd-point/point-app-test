<?php

namespace Point\Core\Helpers;

class QueueHelper
{
    public static function reconnectAppDatabase($database_name)
    {
        \Config::set('database.connections.mysql', array(
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => $database_name,
            'username'  => env('DB_USERNAME'),
            'password'  => env('DB_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => '',
        ));

        \DB::purge('mysql');
        \DB::reconnect('mysql');
    }
}
