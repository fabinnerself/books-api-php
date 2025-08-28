<?php

namespace App\Helpers;

class Validator
{
    private array $errors = [];
    
    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $this->validateField($field, $value, $fieldRules);
        }
        
        return empty($this->errors);
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    private function validateField(string $field, mixed $value, array $rules): void
    {
        foreach ($rules as $rule => $parameter) {
            if (is_numeric($rule)) {
                $rule = $parameter;
                $parameter = null;
            }
            
            switch ($rule) {
                case 'required':
                    if (empty($value) && $value !== '0') {
                        $this->addError($field, "{$field} is required");
                    }
                    break;
                    
                case 'string':
                    if (!is_string($value) && $value !== null) {
                        $this->addError($field, "{$field} must be a string");
                    }
                    break;
                    
                case 'numeric':
                    if (!is_numeric($value) && $value !== null) {
                        $this->addError($field, "{$field} must be numeric");
                    }
                    break;
                    
                case 'min':
                    if (is_string($value) && strlen($value) < $parameter) {
                        $this->addError($field, "{$field} must be at least {$parameter} characters");
                    } elseif (is_numeric($value) && $value < $parameter) {
                        $this->addError($field, "{$field} must be at least {$parameter}");
                    }
                    break;
                    
                case 'max':
                    if (is_string($value) && strlen($value) > $parameter) {
                        $this->addError($field, "{$field} must not exceed {$parameter} characters");
                    } elseif (is_numeric($value) && $value > $parameter) {
                        $this->addError($field, "{$field} must not exceed {$parameter}");
                    }
                    break;
                    
                case 'positive':
                    if (is_numeric($value) && $value <= 0) {
                        $this->addError($field, "{$field} must be positive");
                    }
                    break;
                    
                case 'decimal':
                    if ($value !== null && !preg_match('/^\d+(\.\d{1,2})?$/', (string)$value)) {
                        $this->addError($field, "{$field} must be a valid decimal with max 2 decimal places");
                    }
                    break;
                    
                case 'optional':
                    // Skip validation if field is optional and empty
                    if (empty($value) && $value !== '0') {
                        return;
                    }
                    break;
            }
        }
    }
    
    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }
    
    public static function validateBookData(array $data, bool $isUpdate = false): array
    {
        $validator = new self();
        
        $rules = [
            'name' => $isUpdate ? ['string', 'min' => 3, 'max' => 255] : ['required', 'string', 'min' => 3, 'max' => 255],
            'author' => $isUpdate ? ['string', 'min' => 3, 'max' => 255] : ['required', 'string', 'min' => 3, 'max' => 255],
            'price' => $isUpdate ? ['numeric', 'positive', 'decimal'] : ['required', 'numeric', 'positive', 'decimal'],
            'description' => ['optional', 'string', 'max' => 1000]
        ];
        
        $validator->validate($data, $rules);
        
        return $validator->getErrors();
    }
    
    public static function isValidUuid(string $uuid): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid) === 1;
    }
    
    public static function sanitizeString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }
}