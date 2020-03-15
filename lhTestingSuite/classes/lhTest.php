<?php

/**
 * lhTest - неообходим для усложненной проверки результатов
 * при создании тестов через массив.
 * 
 * Если последним элементом в массиве аргументов тестируемой функции
 * будет указан экземпляр lhTest - для проверки результата будет вызвана
 * функция указанная в конструкторе
 *
 * @author user
 */
class lhTest extends lhSelfTestingClass {
    const EQ = '_EQ_';
    const NE = '_NE_';
    const LT = '_LT_';
    const LE = '_LE_';
    const GT = '_GT_';
    const GE = '_GE_';
    const RANGE = '_RANGE_';
    const PCRE = '_PCRE_';
    const IS_A = '_IS_A_';
    const FIELD = '_FIELD_EQ_';
    const FIELD_EQ = '_FIELD_EQ_';
    const FIELD_NE = '_FIELD_NE_';
    const FIELD_LT = '_FIELD_LT_';
    const FIELD_LE = '_FIELD_LE_';
    const FIELD_GT = '_FIELD_GT_';
    const FIELD_GE = '_FIELD_GE_';
    const FIELD_RANGE = '_FIELD_RANGE_';
    const FIELD_PCRE = '_FIELD_PCRE_';
    const IS_ARRAY = '_IS_ARRAY_';
    const ELEM = '_ELEM_';
    const ELEM_NE = '_ELEM_NE_';
    const ELEM_LT = '_ELEM_LT_';
    const ELEM_LE = '_ELEM_LE_';
    const ELEM_GT = '_ELEM_GT_';
    const ELEM_GE = '_ELEM_GE_';
    const ELEM_RANGE = '_ELEM_RANGE_';
    const ELEM_PCRE = '_ELEM_PCRE_';

    private $func;
    private $args;
    
    public function __construct($_func, ...$_args) {
        $this->func = $_func;
        $this->args = $_args;
    }
    
    public function test($_result) {
        if (is_callable($this->func)) {
            $this->func($_result, ...$this->args);
        } elseif (is_callable([$this, $this->func])) {
            $func = $this->func;
            $this->$func($_result, ...$this->args);
        } else {
            throw new Exception("Constructor argument 1 must be a callable or one of predefined constants");
        }
    }
    
    protected function _EQ_($_result, $_value) {
        if (!($_result == $_value)) {
            throw new Exception($this->_t("Awaiting result to be equals %s but got %s", $_value, $_result), -10002);
        }
    }
    
    protected function _NE_($_result, $_value) {
        if (!($_result != $_value)) {
            throw new Exception($this->_t("Awaiting result not to be equals %s but got %s", $_value, $_result), -10002);
        }
    }
    
    protected function _LT_($_result, $_value) {
        if (!($_result < $_value)) {
            throw new Exception($this->_t("Awaiting result to be less than %s but got %s", $_value, $_result), -10002);
        }
    }

    protected function _LE_($_result, $_value) {
        if (!($_result <= $_value)) {
            throw new Exception($this->_t("Awaiting result to be less than or equals %s but got %s", $_value, $_result), -10002);
        }
    }
    
    protected function _GT_($_result, $_value) {
        if (!($_result > $_value)) {
            throw new Exception($this->_t("Awaiting result to be greater than %s but got %s", $_value, $_result), -10002);
        }
    }

    protected function _GE_($_result, $_value) {
        if (!($_result >= $_value)) {
            throw new Exception($this->_t("Awaiting result to be greater than or equals %s but got %s", $_value, $_result), -10002);
        }
    }
    
    protected function _RANGE_($_result, $_value1, $_value2) {
        if (!($_result >= $_value1)) {
            throw new Exception($this->_t("Awaiting result to be greater than or equals %s but got %s", $_value1, $_result), -10002);
        }
        if (!($_result <= $_value2)) {
            throw new Exception($this->_t("Awaiting result to be less than or equals %s but got %s", $_value2, $_result), -10002);
        }
    }
    
    protected function _PCRE_($_result, $_pattern) {
        if (!preg_match($_pattern, $_result)) {
            throw new Exception($this->_t("Awaiting result to match %s but got %s", $_pattern, $_result), -10002);
        }
    }
    
    protected function _IS_A_($_result, $_class_name) {
        if (!is_a($_result, $_class_name)) {
            throw new Exception($this->_t("Awaiting result to be an instance of %s but got %s", $_class_name, $_result), -10002);
        }
    }
    
    protected function _FIELD_($_result, $_name, $_value) {
        $this->_FIELD_EQ_($_result, $_name, $_value);
    }

    protected function _FIELD_EQ_($_result, $_name, $_value) {
        $this->_EQ_($_result->$_name, $_value);
    }

    protected function _FIELD_NE_($_result, $_name, $_value) {
        $this->_NE_($_result->$_name, $_value);
    }
    
    protected function _FIELD_LT_($_result, $_name, $_value) {
        $this->_LT_($_result->$_name, $_value);
    }

    protected function _FIELD_LE_($_result, $_name, $_value) {
        $this->_LE_($_result->$_name, $_value);
    }
    
    protected function _FIELD_GT_($_result, $_name, $_value) {
        $this->_GT_($_result->$_name, $_value);
    }

    protected function _FIELD_GE_($_result, $_name, $_value) {
        $this->_GE_($_result->$_name, $_value);
    }
    
    protected function _FIELD_RANGE_($_result, $_name, $_value1, $_value2) {
        $this->_RANGE_($_result->$_name, $_value1, $_value2);
    }
    
    protected function _FIELD_PCRE_($_result, $_name, $_pattern) {
        $this->_PCRE_($_result->$_name, $_pattern);
    }
    
    protected function _IS_ARRAY_($_result) {
        if (!is_array($_result)) {
            throw new Exception($this->_t("Awaiting result to be an array, but got %s", $_result), -10002);
        }
    }
    
    protected function _ELEM_($_result, $_name, $_value) {
        $this->_ELEM_EQ_($_result, $_name, $_value);
    }

    protected function _ELEM_EQ_($_result, $_name, $_value) {
        $this->_EQ_($_result[$_name], $_value);
    }

    protected function _ELEM_NE_($_result, $_name, $_value) {
        $this->_NE_($_result[$_name], $_value);
    }
    
    protected function _ELEM_LT_($_result, $_name, $_value) {
        $this->_LT_($_result[$_name], $_value);
    }

    protected function _ELEM_LE_($_result, $_name, $_value) {
        $this->_LE_($_result[$_name], $_value);
    }
    
    protected function _ELEM_GT_($_result, $_name, $_value) {
        $this->_GT_($_result[$_name], $_value);
    }

    protected function _ELEM_GE_($_result, $_name, $_value) {
        $this->_GE_($_result[$_name], $_value);
    }
    
    protected function _ELEM_RANGE_($_result, $_name, $_value1, $_value2) {
        $this->_RANGE_($_result[$_name], $_value1, $_value2);
    }
    
    protected function _ELEM_PCRE_($_result, $_name, $_pattern) {
        $this->_PCRE_($_result[$_name], $_pattern);
    }
    
    protected function _test_data() {
        return [
            'test' => '_test_skip_',
            '_EQ_' => [
                [8, 8, NULL], 
                ['Yes', 'Yes', NULL], 
                ['Good', 'Bad', new Exception("Not equals", -10002)],
                [json_decode('{"value": "some text"}'), json_decode('{"value":"some text"}'), NULL],
                [json_decode('{"value": "some text"}'), json_decode('{"value": "another text"}'), new Exception("Not equals", -10002)],
            ],
            '_NE_' => [
                [8, 8, new Exception("Equals", -10002)], 
                ['Yes', 'Yes', new Exception("Equals", -10002)], 
                ['Good', 'Bad', NULL],
                [json_decode('{"value": "some text"}'), json_decode('{"value":"some text"}'), new Exception("Equals", -10002)],
                [json_decode('{"value": "some text"}'), json_decode('{"value": "another text"}'), NULL],
            ],
            '_LT_' => [
                [8, 8, new Exception("Equals", -10002)], 
                ['Yes', 'Yes', new Exception("Equals", -10002)], 
                ['Good', 'Bad', new Exception("Equals", -10002)],
                ['Bad', 'Good', NULL],
            ],
            '_LE_' => [
                [8, 8, NULL], 
                ['Yes', 'Yes', NULL], 
                ['Good', 'Bad', new Exception("Greater", -10002)],
                ['Bad', 'Good', NULL],
            ],
            '_GT_' => [
                [8, 8, new Exception("Equals", -10002)], 
                ['Yes', 'Yes', new Exception("Equals", -10002)], 
                ['Good', 'Bad', NULL],
                ['Bad', 'Good', new Exception("Less", -10002)],
            ],
            '_GE_' => [
                [8, 8, NULL], 
                ['Yes', 'Yes', NULL], 
                ['Good', 'Bad', NULL],
                ['Bad', 'Good', new Exception("Less", -10002)],
            ],
            '_RANGE_' => [
                [8, 8, 8, NULL], 
                [15, 10, 20, NULL], 
                ['So so', 'Very good', 'Bad', new Exception("Out of range", -10002)],
                ['So so', 'Bad', 'Very good', NULL],
            ],
            '_PCRE_' => [
                [575, "/\\d+/", NULL], 
                [3812, "/\\d{2,3}/", NULL], 
                [3812, "/^\\d{2,3}$/", new Exception("Does not match", -10002)], 
                [3, "/\\d{2,3}/", new Exception("Does not match", -10002)], 
                [18, "/\\d{2,3}/", NULL], 
                ["Hello World", "/d$/", NULL], 
            ],
            '_IS_A_' => [
                [json_decode('{"value": "some text"}'), "stdClass", NULL], 
                [[3812], "Array", new Exception("USE lhTest::IS_ARRAY", -10002)], 
                [3812, "Scalar", new Exception("USE lhTest::RANGE", -10002)], 
                [new lhTest(lhTest::EQ), "lhSelfTestingClass", NULL], 
                [new lhTest(lhTest::EQ), "lhDummy", new Exception("Does not match", -10002)], 
            ],
            '_FIELD_' => [
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value2", "another text", NULL], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value1", 11, new Exception("Not equals", -10002)], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value3", 11, NULL], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value3", "11", NULL], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value3", 15, new Exception("Not equals", -10002)], 
            ],
            '_FIELD_EQ_' => '_test_skip_',  // Tested by _FIELD_
            '_FIELD_NE_' => [
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value2", "another text", new Exception("Equals", -10002)], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value1", 11, NULL], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value3", 11, new Exception("Equals", -10002)], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value3", "11", new Exception("Equals", -10002)], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value3", 15, NULL], 
            ],
            '_FIELD_LT_' => [
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value2", "another text", new Exception("Equals", -10002)], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value1", 11, NULL], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value1", "11", new Exception("Greater", -10002)], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value3", 11, new Exception("Equals", -10002)], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value3", "11", new Exception("Equals", -10002)], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value3", 15, NULL], 
            ],
            '_FIELD_LE_' => [
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value2", "another text", NULL], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value1", 11, NULL], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value1", "11", new Exception("Greater", -10002)], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value3", 11, NULL], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value3", "11", NULL], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value3", 15, NULL], 
            ],
            '_FIELD_GT_' => [
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value2", "another text", new Exception("Equals", -10002)], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value1", 11, new Exception("Less", -10002)], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value1", "11", NULL], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value3", 11, new Exception("Equals", -10002)], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value3", "11", new Exception("Equals", -10002)], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value3", 7, NULL], 
            ],
            '_FIELD_GE_' => [
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value2", "another text", NULL], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value1", 11, new Exception("Less", -10002)], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value1", "11", NULL], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value3", 11, NULL], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value3", "11", NULL], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value3", 7, NULL], 
                [json_decode('{"value1": "some text","value2": "another text", "value3": 11}'), "value3", 15, new Exception("Less", -10002)], 
            ],
            '_FIELD_RANGE_' => [
                [json_decode('{"value": 8}'), "value", 8, 8, NULL], 
                [json_decode('{"value": 15}'), "value", 10, 20, NULL], 
                [json_decode('{"value": "So so"}'), "value", 'Very good', 'Bad', new Exception("Out of range", -10002)],
                [json_decode('{"value": "So so"}'), "value", 'Bad', 'Very good', NULL],
            ],
            '_FIELD_PCRE_' => [
                [json_decode('{"value": 575}'), "value", "/\\d+/", NULL], 
                [json_decode('{"value": 3812}'), "value", "/\\d{2,3}/", NULL], 
                [json_decode('{"value": 3812}'), "value", "/^\\d{2,3}$/", new Exception("Does not match", -10002)], 
                [json_decode('{"value": 3}'), "value", "/\\d{2,3}/", new Exception("Does not match", -10002)], 
                [json_decode('{"value": 18}'), "value", "/\\d{2,3}/", NULL], 
                [json_decode('{"value": "Hello World"}'), "value", "/d$/", NULL], 
            ],
            '_IS_ARRAY_' => [
                [json_decode('{"value": "some text"}'), new Exception("It is not an array", -10002)], 
                [[3812], NULL], 
                [[], NULL], 
                [3812, new Exception("It is not an array", -10002)], 
                [new lhTest(lhTest::EQ), new Exception("It is not an array", -10002)], 
            ],
            '_ELEM_' => [
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value2", "another text", NULL], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value1", 11, new Exception("Not equals", -10002)], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value3", 11, NULL], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value3", "11", NULL], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value3", 15, new Exception("Not equals", -10002)], 
            ],
            '_ELEM_EQ_' => '_test_skip_',  // Tested by _ELEM_
            '_ELEM_NE_' => [
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value2", "another text", new Exception("Equals", -10002)], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value1", 11, NULL], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value3", 11, new Exception("Equals", -10002)], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value3", "11", new Exception("Equals", -10002)], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value3", 15, NULL], 
            ],
            '_ELEM_LT_' => [
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value2", "another text", new Exception("Equals", -10002)], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value1", 11, NULL], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value1", "11", new Exception("Greater", -10002)], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value3", 11, new Exception("Equals", -10002)], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value3", "11", new Exception("Equals", -10002)], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value3", 15, NULL], 
            ],
            '_ELEM_LE_' => [
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value2", "another text", NULL], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value1", 11, NULL], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value1", "11", new Exception("Greater", -10002)], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value3", 11, NULL], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value3", "11", NULL], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value3", 15, NULL], 
            ],
            '_ELEM_GT_' => [
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value2", "another text", new Exception("Equals", -10002)], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value1", 11, new Exception("Less", -10002)], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value1", "11", NULL], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value3", 11, new Exception("Equals", -10002)], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value3", "11", new Exception("Equals", -10002)], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value3", 7, NULL], 
            ],
            '_ELEM_GE_' => [
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value2", "another text", NULL], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value1", 11, new Exception("Less", -10002)], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value1", "11", NULL], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value3", 11, NULL], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value3", "11", NULL], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value3", 7, NULL], 
                [["value1"=>"some text","value2"=>"another text","value3"=>11], "value3", 15, new Exception("Less", -10002)], 
            ],
            '_ELEM_RANGE_' => [
                [["value"=>8], "value", 8, 8, NULL], 
                [["value"=>15], "value", 10, 20, NULL], 
                [["value"=>"So so"], "value", 'Very good', 'Bad', new Exception("Out of range", -10002)],
                [["value"=>"So so"], "value", 'Bad', 'Very good', NULL],
            ],
            '_ELEM_PCRE_' => [
                [["value"=>575], "value", "/\\d+/", NULL], 
                [["value"=>3812], "value", "/\\d{2,3}/", NULL], 
                [["value"=>3812], "value", "/^\\d{2,3}$/", new Exception("Does not match", -10002)], 
                [["value"=>3], "value", "/\\d{2,3}/", new Exception("Does not match", -10002)], 
                [["value"=>18], "value", "/\\d{2,3}/", NULL], 
                [["value"=>"Hello World"], "value", "/d$/", NULL], 
            ],
        ];
    }

}