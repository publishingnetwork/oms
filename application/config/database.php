<?php


return array
(
    'default' => array
    (
        'type'       => 'MySQL',
        'connection' => array(
            'hostname'   => '127.0.0.1',
            'username'   => 'root',
            'password'   => '1234',
            'persistent' => FALSE,
            'database'   => 'yeastinf_oms',
        ),
        'table_prefix' => '',
        'charset'      => 'utf8',
        'time_zone'    => '+00:00',
        'profiling'    => TRUE,
    ),

);