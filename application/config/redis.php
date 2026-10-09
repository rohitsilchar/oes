<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| Redis Settings
| -------------------------------------------------------------------
| Configuration for CodeIgniter's native Redis driver and Fast_cache
| library.
*/

$config['socket_type'] = 'tcp'; // 'tcp' or 'unix'
$config['host']        = '127.0.0.1';
$config['password']    = NULL;
$config['port']        = 6379;
$config['timeout']     = 1; // 1s timeout to ensure no latency if Redis is offline
$config['default_ttl'] = 300; // 5 minutes default cache lifetime
