<?php

namespace App\Core;

class RequestValidator
{
    private static $instance = null;
    private $errors = [];

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function validate($data, $rules)
    {
        $this->errors = [];

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (strpos($rule, 'required') !== false && empty($value)) {
                $this->errors[$field] = "The {$field} field is required.";
                continue;
            }

            if (!empty($value)) {
                $ruleArray = explode('|', $rule);
                
                foreach ($ruleArray as $singleRule) {
                    if (strpos($singleRule, ':') !== false) {
                        list($ruleName, $ruleValue) = explode(':', $singleRule);
                    } else {
                        $ruleName = $singleRule;
                        $ruleValue = null;
                    }

                    switch ($ruleName) {
                        case 'email':
                            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                $this->errors[$field] = "The {$field} must be a valid email address.";
                            }
                            break;

                        case 'min':
                            if (strlen($value) < $ruleValue) {
                                $this->errors[$field] = "The {$field} must be at least {$ruleValue} characters.";
                            }
                            break;

                        case 'max':
                            if (strlen($value) > $ruleValue) {
                                $this->errors[$field] = "The {$field} must not exceed {$ruleValue} characters.";
                            }
                            break;

                        case 'numeric':
                            if (!is_numeric($value)) {
                                $this->errors[$field] = "The {$field} must be a number.";
                            }
                            break;

                        case 'in':
                            $allowedValues = explode(',', $ruleValue);
                            if (!in_array($value, $allowedValues)) {
                                $this->errors[$field] = "The {$field} must be one of: " . implode(', ', $allowedValues);
                            }
                            break;

                        case 'regex':
                            if (!preg_match($ruleValue, $value)) {
                                $this->errors[$field] = "The {$field} format is invalid.";
                            }
                            break;
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }
} 