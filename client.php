<?php
$cli = new swoole_http_client('52.221.147.32', 8080);

$cli->on('message', function ($_cli, $frame) {
    var_dump($frame);
});

$cli->upgrade('/', function ($cli) {
    $cli->push("hello world");
});