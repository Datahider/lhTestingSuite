<?php
/*
 * include this file in class that have to be self testing
 *  
 */

    protected function _test_skip_() {
        echo '.пропущено.';
    }

    public function _test() {
        $class_name = get_class();
        $class_metods = get_class_methods($class_name);
        if (false === array_search("_test_data", get_class_methods($class_name)))
            throw new Exception("Function _test_data does not exist in class $class_name", -907); 
        foreach ($class_methods as $key) {
            echo "Функция $key.";
            if (preg_match("/^_test/", $key) || preg_match("/__construct/", $key)) { 
                echo ".. пропущена\n";
                continue; 
            }
            
            $test_data = $this->_test_data();
            if (!isset($test_data[$key])) {
                throw new Exception("No test definition for member function $key");
            }
            $test_args = $test_data[$key];
            
            
            if (!is_array($test_args)) {
                if (!preg_match("/^_test/", $test_args)) {
                    throw new Exception("Test function name for $class_name::$key() must start with _test");
                }
                $func = $test_args;
                $this->$func();
                echo ". ok\n";
            } else {
                foreach ($test_args as $args) {
                    $await = array_pop($args);
                    try {
                        $result = $this->$key(...$args);
                        if (is_a($await, 'Exception')) {
                            throw new Exception("Awaiting an Exception with code: ".$await->getCode()." but did not got it", -907);
                        }
                        if ($result != $await) {
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
