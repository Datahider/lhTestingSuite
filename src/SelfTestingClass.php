<?php

/**
 * Прародитель всех самотестирующихся классов
 * Содержит функции для тестирования
 * Как и что делать можно разобраться просто сделав этот класс родительским
 * И вызвав $ваш_класс->_test()
 *
 * Для отладочного логирования используйте $this->log(...) и $this->logStatic(...)
 * где второй отправляет сообщение в лог всегда, а первый только при установленном
 * уровне отладочного логирования (константа NAMESPACE_NAME_YOURCLASS_DEBUG_LEVEL)
 * @author Petros Ioannidis <pio@pio.su>
 */

namespace losthost\SelfTestingSuite;

class SelfTestingClass {
    static public $logfile;
    
    private $test_data;
    private $tests;
    private $methods;
    private $func;
    private $iteration;

    protected function log($_message='call', $_level=10) {
        $class = \get_class($this);
        $const_name = \str_replace("\\", "_", \strtoupper($class). '_DEBUG_LEVEL');
        $level = \defined($const_name) ? \constant($const_name) : 0; 
        if ($_level <= $level) {
            $debug = \debug_backtrace(false, 2);
            $function = $debug[1]['class']. '->'. $debug[1]['function'];
            $log_message = \is_scalar($_message) ? $_message : print_r($_message, true);
            $mem = \memory_get_usage();
            $log_message = "|$mem| $function - $log_message";
            if (SelfTestingClass::$logfile) {
                $log_file = fopen(SelfTestingClass::$logfile, 'a');
                fwrite($log_file, date(DATE_ISO8601).": ${class}[$_level]:: $log_message\n"); 
                fclose($log_file);
            } else {
                error_log("${class}[$level]:: $log_message");
            }
        }
    }

    public static function logStatic($_message) {
        $log_message = is_scalar($_message) ? $_message : print_r($_message, true);
        $mem = memory_get_usage();
        $log_message = "|$mem| - $log_message";
        if (SelfTestingClass::$logfile) {
            $log_file = fopen(SelfTestingClass::$logfile, 'a');
            fwrite($log_file, date(DATE_ISO8601).": STATIC:: $log_message\n"); 
            fclose($log_file);
        } else {
            error_log("STATIC:: $log_message");
        }
    }

    protected function _test_data() {
        $this->log();
        throw new \Exception("YourClass->_test_data() must return an array alike:\n"
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
        $this->log();
        if (\is_scalar($args[0]) && \preg_match("/\%s/", $args[0])) {
            // New behavior
            $format = \array_shift($args);
            $strings = [];
            foreach ($args as $value) {
                if (\is_scalar($value)) {
                    $strings[] = $value;
                } else {
                    $strings[] = \print_r($value, TRUE);
                }
            }
            return \sprintf($format, ...$strings);
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
        $this->log();
        try {
            return $this->$func(...$args);
        } catch (\Exception $exc) {
            return $exc;
        }
    }

    protected function _test_skip_() {
        $this->log();
        echo ' skipped';
    }
    
    public function _test() {
        $this->log();
        $this->test_data = $this->_test_data();
        $this->tests = array_keys($this->test_data);
        $this->methods = get_class_methods($this);
        
        $result = $this->_test_runTests();
        
        if ($result === NULL) {
            throw new \Exception("Please implement " . __CLASS__ . '::' . __FUNCTION__ . '(...)');
        }
        return $result;
    }
    
    private function _test_runTests() {
        $this->log();
        echo sprintf("\nTesting %s...\n", get_class($this));
        while ($this->func = array_shift($this->tests)) {
            echo "function $this->func";
            if (array_search($this->func, $this->methods, true) === false) {
                throw new \Exception("Member function $this->func does not exist.", 981239);
            }
            $this->iteration = 0;
            $this->_test_doTest();
            unset($this->test_data[$this->func]);
            $index = array_search($this->func, $this->methods);
            if ($index !== false) {
                unset($this->methods[$index]);
            }
            echo " ok\n";
        }
        $result = $this->_test_finalCheck();
        if ($result === NULL) {
            throw new \Exception("Please implement " . __CLASS__ . '::' . __FUNCTION__ . '(...)');
        }
        return $result;
    }

    private function _test_doTest() {
        $this->log();
        $test_set = $this->test_data[$this->func];
        if (is_array($test_set)) {
            $this->_test_doSimpleTest($test_set);
        } else {
            if (!preg_match("/^_test/", $test_set)) {
                throw new \Exception("Test function names have to be started with _test but got $test_set");
            }
            $this->$test_set();
        }
    }
    
    private function _test_doSimpleTest($_test_set) {
        $this->log();
        foreach ($_test_set as $arg_set) {
            $this->iteration++; echo '.';
            $await = array_pop($arg_set);
            $args = $arg_set;

            if (!is_a($await, 'losthost\SelfTestingSuite\Test') && !is_a($await, '\Exception')) {
                $await = new Test(Test::EQ, $await);
            }
        
            $this->_test_checkResult($this->_test_call($this->func, $args), $await);
        }
        return TRUE;
    }

    protected function _test_checkResult($_result, $_await) {
        $this->log();

        if (is_a($_await, '\Exception')) {
            return $this->_test_checkException($_result, $_await);
        } else {
            $_await->test($_result);
            return TRUE;
        }
    }

    private function _test_checkException($_result, $_await) {
        $this->log();
        if (is_a($_result, '\Exception')) {
            return $this->_test_checkExceptionCode($_result, $_await);
        } else {
            throw new \Exception($this->_t(
                "$this->func\[$this->iteration\]: awaiting an Exception with code %s but didn't got it", 
                $_await->getCode()
            ), -907);
        }
    }
    
    private function _test_checkExceptionCode($_result, $_await) {
        $this->log();
        if ($_result->getCode() == $_await->getCode()) {
            return TRUE;
        } else {
            throw new \Exception($this->_t(
                "$this->func\[$this->iteration\]: awaiting an Exception with code %s but got code %s\n%s", 
                $_await->getCode(), 
                $_result->getCode(), 
                $_result
            ));
        }
    }
    
    private function _test_finalCheck() {
        $this->log();
        $did_not_tested = [];
        while ($check = array_shift($this->methods)) {
            if ($check == '__construct') continue;
            if ($check == 'log') continue;
            if ($check == 'logFunction') continue;
            if ($check == 'logStatic') continue;
            if ($check == '_t') continue;
            if (preg_match("/^_test/", $check)) continue;
            $did_not_tested[] = $check;
        }
        if (count($did_not_tested)) {
            throw new \Exception("You have no test definitions for theese functions:\n". print_r($did_not_tested, TRUE), -10001);
        }
        return TRUE;
    }


}
