<?php
/**
 *  Prepares sample configuration
 */

$source = __DIR__ . '/tests/samples/sample.ini';
$dest = 'config.ini';
copy($source, $dest);

$source = __DIR__ . '/developer_assistant.php';
$dest = 'developer_assistant.php';
copy($source, $dest);
