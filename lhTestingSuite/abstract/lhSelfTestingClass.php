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
    
    protected function _t(...$args) {
        if (is_scalar($args[0]) && preg_match("/\%s/", $args[0])) {
            // New behavior
            $format = array_shift($args);
            $strings = [];
            foreach ($args as $value) {
                if (is_scalar($value)) {
                    $strings[] = $value;
                } else {
                    $strings[] = print_r($value, TRUE);
                }
            }
            return sprintf($format, ...$strings);
        } else {
            // Backward compatibility
            $text = '';
            foreach ($args as $a) {
                if (is_scalar($a)) {
                    $text .= $a;
                } else {
                    $text .= print_r($a, TRUE);
                }
            }
            return trim($text);
        }
    }

    protected function _test_call($func, ...$args) {
        return $this->$func(...$args);
    }

    protected function _test_skip_() {
        echo '.skipped.';
    }

    public function _test() {
        $class_name = get_class($this);
        echo "\n\nStarting tests for class $class_name...\n";
        $class_methods = get_class_methods($class_name);
        if (false === array_search("_test_data", get_class_methods($class_name)))
            throw new Exception("Function _test_data does not exist in class $class_name", -10001); 

        $test_data = $this->_test_data();
        foreach ($class_methods as $key) {
            if (preg_match("/^_test/", $key) || preg_match("/^__construct$/", $key) || preg_match("/^_t$/", $key)) { 
                continue; 
            }

            if (!isset($test_data[$key])) {
                throw new Exception("No test definition for $class_name->$key", -10001);
            }

            echo "function $key.";
            $test_args = $test_data[$key];
            
            if (!is_array($test_args)) {
                if (!preg_match("/^_test/", $test_args)) {
                    throw new Exception("Test function name for $class_name->$key() must start with _test");
                }
                $func = $test_args;
                $this->_test_call($func);
                echo ". ok\n";
            } else {
                foreach ($test_args as $args) {
                    if (count($args) == 0) {
                        throw new Exception($this->_t("Test array must have at least one element. Got: %s", $args));
                    }
                    $await = array_pop($args);
                    try {
                        $result = $this->_test_call($key, ...$args);
                        if (is_a($await, 'Exception')) {
                            throw new Exception("Awaiting an Exception with code: ".$await->getCode()." but did not got it", -907);
                        }
                        if ($result != $await) {
                            if (is_object($await) || is_array($await)) {
                                $await = print_r($await, TRUE);
                            }
                            throw new Exception($this->_t("Wrong result: ", $result, ", awaiting: ", $await), -907);
                        }
                    } catch (Exception $e) {
                        if ($e->getCode() == -907) throw $e;
                        if (!is_a($await, "Exception") || ($e->getCode() != $await->getCode()) ) {
                            throw new Exception("Invalid Exception with code: (".$e->getCode().")\n"
                                    . "It goes from line ".$e->getLine().' of '.$e->getFile()."\n"
                                    . "Message is: ".$e->getMessage()."\n"
                                    . "Trace:\n".$e->getTraceAsString());
                        }
                    }
                    echo '.';
                }
                echo ". ok\n";
            }
        }
        echo "$class_name tested.. ok\n";
        return TRUE;
    }
}
