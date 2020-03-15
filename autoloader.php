<?php

spl_autoload_register(function ($class) {
    $suggested = [
        __DIR__ . "/lhTestingSuite/classes/$class.php"
    ];
    
    foreach ($suggested as $file) {
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

