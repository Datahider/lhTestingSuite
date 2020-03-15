<?php

require_once __DIR__ . '/autoloader.php';

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
