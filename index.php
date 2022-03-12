<?php
define('LHSELFTESTINGCLASS_DEBUG_LEVEL', 10);
define('LHTEST_DEBUG_LEVEL', 10);
require_once __DIR__ . '/autoloader.php';
require_once __DIR__. '/localsettings.php'; // Ex. lhSelfTestingClass::$logfile = 'some_path'; and/or date_default_timezone_set('Europe/Moscow');

try {
    $test = new lhTest(lhTest::EQ, 0);
    $test->_test();
} catch (Exception $e) {
    echo "\n\n";
    echo 'Ошибка '.$e->getCode();
    echo ' - '.$e->getMessage();
    echo "\n\nСтрока ".$e->getLine().' в файле '.$e->getFile();
    echo "\n\nТрассировка:\n".$e->getTraceAsString();
    echo "\n\nТЕСТИРОВАНИЕ ЗАВЕРШЕНО С ОШИБКОЙ";
}
