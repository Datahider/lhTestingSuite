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
    const DEBUG_LEVEL = 10;

    static public $logfile;
    
    private $test_data;
    private $tests;
    private $methods;
    private $func;
    private $iteration;

    protected function log($_message, $_level=10) {
        $class = get_class($this);
        $level = $class::DEBUG_LEVEL;
        if ($_level <= $level) {
            $log_message = is_scalar($_message) ? $_message : print_r($_message, true);
            if (lhSelfTestingClass::$logfile) {
                $log_file = fopen(lhSelfTestingClass::$logfile, 'a');
                fwrite($log_file, date(DATE_ISO8601).": ${class}[$level]:: $log_message\n"); 
                fclose($log_file);
            } else {
                error_log("$class\[$level\]:: $log_message");
            }
        }
    }

    
    protected function _test_data() {
        $this->log(__FUNCTION__);
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
        $this->log(__FUNCTION__);
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

    protected function _test_call($func, $args) { // Copy this to your class to test private methods
        $this->log(__FUNCTION__);
        try {
            return $this->$func(...$args);
        } catch (Exception $exc) {
            return $exc;
        }
    }

    protected function _test_skip_() {
        $this->log(__FUNCTION__);
        echo ' skipped';
    }
    
    public function _test() {
        $this->log(__FUNCTION__);
        $this->test_data = $this->_test_data();
        $this->tests = array_keys($this->test_data);
        $this->methods = get_class_methods($this);
        
        $result = $this->_test_runTests();
        
        if ($result === NULL) {
            throw new Exception("Please implement " . __CLASS__ . '::' . __FUNCTION__ . '(...)');
        }
        return $result;
    }
    
    private function _test_runTests() {
        $this->log(__FUNCTION__);
        while ($this->func = array_shift($this->tests)) {
            echo "function $this->func";
            $this->iteration = 0;
            $this->_test_doTest();
            unset($this->test_data[$this->func]);
            unset($this->methods[array_search($this->func, $this->methods)]);
            echo " ok\n";
        }
        $result = $this->_test_finalCheck();
        if ($result === NULL) {
            throw new Exception("Please implement " . __CLASS__ . '::' . __FUNCTION__ . '(...)');
        }
        return $result;
    }

    private function _test_doTest() {
        $this->log(__FUNCTION__);
        $test_set = $this->test_data[$this->func];
        if (is_array($test_set)) {
            $this->_test_doSimpleTest($test_set);
        } else {
            if (!preg_match("/^_test/", $test_set)) {
                throw new Exception("Test function names have to be started with _test but got $test_set");
            }
            $this->$test_set();
        }
    }
    
    private function _test_doSimpleTest($_test_set) {
        $this->log(__FUNCTION__);
        foreach ($_test_set as $arg_set) {
            $this->iteration++; echo '.';
            $await = array_pop($arg_set);
            $args = $arg_set;

            if (!is_a($await, 'lhTest') && !is_a($await, 'Exception')) {
                $await = new lhTest(lhTest::EQ, $await);
            }
        
            $this->_test_checkResult($this->_test_call($this->func, $args), $await);
        }
        return TRUE;
    }

        private function _test_checkResult($_result, $_await) {
        $this->log(__FUNCTION__);

        if (is_a($_await, 'Exception')) {
            return $this->_test_checkException($_result, $_await);
        } else {
            $_await->test($_result);
            return TRUE;
        }
    }

    private function _test_checkException($_result, $_await) {
        $this->log(__FUNCTION__);
        if (is_a($_result, 'Exception')) {
            return $this->_test_checkExceptionCode($_result, $_await);
        } else {
            throw new Exception($this->_t(
                "$this->func\[$this->iteration\]: awaiting an Exception with code %s but didn't got it", 
                $_await->getCode()
            ), -907);
        }
    }
    
    private function _test_checkExceptionCode($_result, $_await) {
        $this->log(__FUNCTION__);
        if ($_result->getCode() == $_await->getCode()) {
            return TRUE;
        } else {
            throw new Exception($this->_t(
                "$this->func\[$this->iteration\]: awaiting an Exception with code %s but got code %s\n%s", 
                $_await->getCode(), 
                $_result->getCode(), 
                $_result
            ));
        }
    }
    
    private function _test_finalCheck() {
        $this->log(__FUNCTION__);
        $did_not_tested = [];
        while ($check = array_shift($this->methods)) {
            if ($check == '__construct') continue;
            if ($check == 'log') continue;
            if ($check == '_t') continue;
            if (preg_match("/^_test/", $check)) continue;
        }
        if (count($this->methods)) {
            throw new Exception("Some metods have not tested:\n". print_r($this->methods, TRUE));
        }
        return TRUE;
    }


}
