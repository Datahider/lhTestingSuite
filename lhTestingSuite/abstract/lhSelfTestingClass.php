<?php

/**
 * Прародитель всех самотестирующихся классов
 * Содержит функции для тестирования
 * Как и что делать можно разобраться просто сделав этот класс родительским
 * И вызвав $ваш_класс->_test()
 *
 * @author user
 */
class lhSelfTestingClass {

    protected function _test_data() {
        throw new Exception("YourClass->_test_data() must return an array alike:\n"
                . "[\n"
                . "  'simple_function_name' => [\n"
                . "    [\$arg1, \$arg2, \$arg3, \$result],\n"
                . "    [\$arg1, \$result]\n"
                . "  ],\n"
                . "  'more_complex_function' => '_test_more_complex_function',\n"
                . "  'another_function' => '_test_skip_'\n"
                . "];");
    }
    
    protected function _test_call($func, ...$args) {
        $this->$func(...$args);
    }

    protected function _test_skip_() {
        echo '.skipped.';
    }

    public function _test() {
        $class_name = get_class($this);
        $class_methods = get_class_methods($class_name);
        if (false === array_search("_test_data", get_class_methods($class_name)))
            throw new Exception("Function _test_data does not exist in class $class_name", -907); 

        $test_data = $this->_test_data();
        foreach ($class_methods as $key) {
            echo "function $key.";
            if (preg_match("/^_test/", $key) || preg_match("/__construct/", $key)) { 
                echo "..skipped..ok\n";
                continue; 
            }
            
            if (!isset($test_data[$key])) {
                throw new Exception("No test definition for member function $key");
            }
            $test_args = $test_data[$key];
            
            
            if (!is_array($test_args)) {
                if (!preg_match("/^_test/", $test_args)) {
                    throw new Exception("Test function name for $class_name::$key() must start with _test");
                }
                $func = $test_args;
                $this->_test_call($func);
                echo ". ok\n";
            } else {
                foreach ($test_args as $args) {
                    $await = array_pop($args);
                    try {
                        $result = $this->_test_call($key, $args);
                        if (is_a($await, 'Exception')) {
                            throw new Exception("Awaiting an Exception with code: ".$await->getCode()." but did not got it", -907);
                        }
                        if ($result != $await) {
                            if (is_object($await) || is_array($await)) {
                                $await = print_r($await, TRUE);
                            }
                            throw new Exception("Wrong result: $result, awaiting: $await", -907);
                        }
                    } catch (Exception $e) {
                        if ($e->getCode() == -907) throw $e;
                        if (!is_a($await, "Exception") || ($e->getCode() != $await->getCode()) ) {
                            throw new Exception("Invalid Exception with code: (".$e->getCode().") ".$e->getMessage());
                        }
                    }
                    echo '.';
                }
                echo ". ok\n";
            }
        }
        return TRUE;
    }
}
