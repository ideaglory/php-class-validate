<?php

/**
 * Custom Request Validation and Data Handling
 * 
 * A lightweight PHP class for validating various data types and values, 
 * including emails, numbers, and strings. This class provides a simple 
 * way to validate input and ensure data correctness in PHP applications.
 *
 * Author: Ideaglory
 * GitHub: https://github.com/ideaglory/php-class-validate
 * 
 */

class Validate
{
    private  $data = [];          // Holds the request data
    private  $errors = [];        // Holds validation errors
    private  $rules = [];         // Validation rules
    private  $messages = [];      // Custom error messages
    private  $customRules = [];   // Custom validation rules
    private  $defaults = [];      // Default values for fields

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Define validation rules for the request.
     *
     * @param array $rules
     * @return void
     */
    public function setRules(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * Define custom error messages for the request.
     *
     * @param array $messages
     * @return void
     */
    public function setMessages(array $messages)
    {
        $this->messages = $messages;
    }

    /**
     * Register a custom validation rule.
     *
     * @param string $ruleName
     * @param callable $callback
     * @return void
     */
    public function addCustomRule(string $ruleName, callable $callback)
    {
        $this->customRules[$ruleName] = $callback;
    }

    /**
     * Set default values for fields if they are missing.
     *
     * @param array $defaults
     * @return void
     */
    public function setDefaults(array $defaults)
    {
        $this->defaults = $defaults;

        foreach ($this->defaults as $field => $default) {
            if (!isset($this->data[$field])) {
                $this->data[$field] = $default;
            }
        }
    }

    /**
     * Validate the request data.
     *
     * @return bool
     */
    public function validate(): bool
    {
        foreach ($this->rules as $field => $rules) {
            $value = $this->getValueByPath($field, $this->data);

            foreach (explode('|', $rules) as $rule) {
                if (strpos($rule, ':')) {
                    [$ruleName, $ruleParam] = explode(':', $rule);
                    $this->applyRule($field, $ruleName, $value, $ruleParam);
                } else {
                    $this->applyRule($field, $rule, $value);
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Apply a single validation rule.
     *
     * @param string $field
     * @param string $rule
     * @param mixed $value
     * @param mixed|null $param
     * @return void
     */
    private function applyRule(string $field, string $rule, $value, $param = null)
    {
        // Handle custom rules
        if (isset($this->customRules[$rule])) {
            $isValid = call_user_func($this->customRules[$rule], $value, $param);

            if (!$isValid) {
                $this->addError($field, $this->getErrorMessage($field, $rule, "$field validation failed."));
            }
            return;
        }

        // Handle built-in rules
        switch ($rule) {
            case 'required':
                if ($value === null || trim($value) === "") {
                    $this->addError($field, $this->getErrorMessage($field, $rule, "$field is required."));
                }
                break;
            case 'string':
                if (!is_string($value)) {
                    $this->addError($field, $this->getErrorMessage($field, $rule, "$field must be a string."));
                }
                break;
            case 'integer':
                if (filter_var(trim($value), FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, $this->getErrorMessage($field, $rule, "$field must be an integer."));
                }
                break;
            case 'min':
                if (is_numeric($value)) {
                    if ($value < (int) $param) {
                        $this->addError($field, $this->getErrorMessage($field, $rule, "$field must be at least $param."));
                    }
                } elseif (is_string($value)) {
                    if (strlen($value) < (int) $param) {
                        $this->addError($field, $this->getErrorMessage($field, $rule, "$field must be at least $param characters."));
                    }
                }
                break;
            case 'max':
                if (is_numeric($value)) {
                    if ($value > (int) $param) {
                        $this->addError($field, $this->getErrorMessage($field, $rule, "$field must not exceed $param."));
                    }
                } elseif (is_string($value)) {
                    if (strlen($value) > (int) $param) {
                        $this->addError($field, $this->getErrorMessage($field, $rule, "$field must not exceed $param characters."));
                    }
                }
                break;
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, $this->getErrorMessage($field, $rule, "$field must be a valid email."));
                }
                break;
            case 'boolean':
                if (!is_bool($value)) {
                    $this->addError($field, $this->getErrorMessage($field, $rule, "$field must be a boolean value."));
                }
                break;
            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, $this->getErrorMessage($field, $rule, "$field must be a valid URL."));
                }
                break;
            case 'alpha':
                if (!preg_match('/^[a-zA-Z]+$/', $value)) {
                    $this->addError($field, $this->getErrorMessage($field, $rule, "$field must contain only alphabetic characters."));
                }
                break;
            case 'alpha_dash':
                if (!preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
                    $this->addError($field, $this->getErrorMessage($field, $rule, "$field must contain only alphanumeric characters, dashes, and underscores."));
                }
                break;
            case 'numeric':
                if (!is_numeric($value)) {
                    $this->addError($field, $this->getErrorMessage($field, $rule, "$field must be numeric."));
                }
                break;
            case 'equal':
                $compareFieldValue = $this->getValueByPath($param, $this->data);
                if ($value !== $compareFieldValue) {
                    $this->addError($field, $this->getErrorMessage($field, $rule, "$field must be equal to $param."));
                }
                break;
            case 'in':
                if (!in_array($value, explode(',', $param))) {
                    $this->addError($field, $this->getErrorMessage($field, $rule, "$field must be one of the following values: $param."));
                }
                break;
            case 'not_in':
                if (in_array($value, explode(',', $param))) {
                    $this->addError($field, $this->getErrorMessage($field, $rule, "$field must not be one of the following values: $param."));
                }
                break;
            case 'date':
                $date = date_create($value);
                if (!$date || $date->format('Y-m-d') !== $value) {
                    $this->addError($field, $this->getErrorMessage($field, $rule, "$field must be a valid date."));
                }
                break;
            default:
                $this->addError($field, $this->getErrorMessage($field, $rule, "Invalid rule: $rule."));
        }
    }

    /**
     * Get the error message for a validation rule.
     *
     * @param string $field
     * @param string $rule
     * @param string $default
     * @return string
     */
    private function getErrorMessage(string $field, string $rule, string $default): string
    {
        return $this->messages["$field.$rule"] ?? $default;
    }

    /**
     * Add an error to the errors array.
     *
     * @param string $field
     * @param string $message
     * @return void
     */
    private function addError(string $field, string $message)
    {
        $this->errors[$field][] = $message;
    }

    /**
     * Get validation errors.
     *
     * @return array
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get the sanitized data.
     *
     * @return array
     */
    public function sanitized(): array
    {
        return $this->sanitize($this->data);
    }

    /**
     * Sanitize the input data.
     *
     * @param array $data
     * @return array
     */
    private function sanitize(array $data): array
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            }
            return $value;
        }, $data);
    }

    /**
     * Get a value from nested data using dot notation.
     *
     * @param string $path
     * @param array $data
     * @return mixed|null
     */
    private function getValueByPath(string $path, array $data)
    {
        $keys = explode('.', $path);
        foreach ($keys as $key) {
            if (!isset($data[$key])) {
                return null;
            }
            $data = $data[$key];
        }
        return $data;
    }
}

/**
 *
 * This class is a custom request validation and data handling class. 
 * Its primary function is to handle incoming user data, validate that data 
 * based on defined rules, provide meaningful error messages for any validation 
 * failures, and optionally sanitize the input data.
 * 
 * The class is designed to be flexible, allowing for:
 * - Standard and custom validation rules.
 * - Error handling with detailed validation failure messages.
 * - Input sanitization, such as trimming strings and cleaning data.
 * 
 * This class ensures that only validated and sanitized data is processed, 
 * providing a clean and reliable input handling system for your application.
 * 

 * Available Validation Rules:
 *
 * - 'required'    : Ensures the field is not empty.
 * - 'string'      : Ensures the field is a string.
 * - 'integer'     : Ensures the field is an integer.
 * - 'min'         : Ensures the field value is at least a specified value (numeric or string length).
 * - 'max'         : Ensures the field value does not exceed a specified value (numeric or string length).
 * - 'email'       : Ensures the field is a valid email address.
 * - 'boolean'     : Ensures the field is a boolean value (true or false).
 * - 'url'         : Ensures the field is a valid URL.
 * - 'alpha'       : Ensures the field contains only alphabetic characters (A-Z, a-z).
 * - 'alpha_dash'  : Ensures the field contains only alphanumeric characters, dashes, and underscores.
 * - 'numeric'     : Ensures the field is numeric (integer or float).
 * - 'equal'       : Ensures the field is equal to another field.
 * - 'in'          : Ensures the field value is one of the specified values (comma-separated).
 * - 'not_in'      : Ensures the field value is not one of the specified values (comma-separated).
 * - 'date'        : Ensures the field is a valid date (in 'Y-m-d' format).
 *
 * Each rule checks a specific condition on the field and returns an error message if the condition is not met.
 * Rules may also accept parameters (e.g., 'min', 'max', 'in', 'not_in') which provide additional validation criteria.
 * 
 * EXAMPLES
 * 
 */
/*

---------------------- Example 1: Simple Validation ----------------------
$data = [
    'name' => 'John_Doe',
    'emails' => ['email' => 'john.doe@example.com', 'email_confirm' => 'john.doe@example.com'],
    'age' => 25,
    'active' => true,
    'website' => 'https://example.com',
    'birthdate' => '1999-12-31',
    'category' => 'technology',
    'status' => 'inactive',
    'first_name' => 'John',
    'last_name' => 'Doe', 
];

$validate = new Validate($data);

$validate->setDefaults([
    'age' => 30,
    'status' => 'active'
]);

$validate->setRules([
    'name' => 'required|string|alpha_dash',       // name must be required, string, and alpha_dash
    'emails.email' => 'required|email',                  // email must be required and a valid email
    'emails.email_confirm' => 'required|email|equal:emails.email', // email_confirm must be required and a valid email, also need match emails.email
    'age' => 'required|numeric|min:18|max:60',    // age must be required, numeric, min 18, max 60
    'active' => 'required|boolean',               // active must be required and a boolean
    'website' => 'required|url',                  // website must be required and a valid URL
    'birthdate' => 'required|date',               // birthdate must be required and a valid date
    'category' => 'required|in:technology,health,education', // category must be in the specified values
    'status' => 'required|not_in:suspended,deleted', // status must be not one of the disallowed values
    'first_name' => 'required|alpha',             // first_name must be required and only alphabetic characters
    'last_name' => 'required|string|max:20|min:3', // last_name must be string, min 3 chars, max 20 chars
]);

$validate->setMessages([
    'name.required' => 'The name is mandatory.',
    'name.alpha_dash' => 'The name can only contain letters, numbers, dashes, and underscores.',
    'emails.email.required' => 'The email is mandatory.',
    'emails.email.email' => 'The email must be a valid email address.',
    'emails.email_confirm.equal' => 'Emails does not match.',
    'age.required' => 'The age is mandatory.',
    'age.numeric' => 'The age must be a number.',
    'age.min' => 'The age must be at least 18.',
    'age.max' => 'The age must not exceed 60.',
    'active.required' => 'The active status is mandatory.',
    'active.boolean' => 'The active status must be true or false.',
    'website.required' => 'The website is mandatory.',
    'website.url' => 'The website must be a valid URL.',
    'birthdate.required' => 'The birthdate is mandatory.',
    'birthdate.date' => 'The birthdate must be a valid date.',
    'category.required' => 'The category is mandatory.',
    'category.in' => 'The category must be one of the following: technology, health, education.',
    'status.required' => 'The status is mandatory.',
    'status.not_in' => 'The status must not be one of the following: suspended, deleted.',
    'first_name.required' => 'The first name is mandatory.',
    'first_name.alpha' => 'The first name must contain only alphabetic characters.',
    'last_name.required' => 'The last name is mandatory.',
    'last_name.string' => 'The last name must be a string.',
    'last_name.min' => 'The last name must be at least 3 characters.',
    'last_name.max' => 'The last name must not exceed 20 characters.',
]);

if ($validate->validate()) {
    echo "Validation passed!";
    $data = $validate->sanitized(); // Replace the orginal data with sanitized data optionally
} else {
    print_r($validate->errors());
}

-------------------- Example 2: Custom Validation Rule -------------------

$data = ['number' => 8];
$validate = new Validate($data);
$validate->setRules([
    'number' => 'required|even'
]);

$validate->addCustomRule('even', function ($value) {
    return $value % 2 === 0;
});

if ($validate->validate()) {
    print_r($validate->sanitized());
} else {
    print_r($validate->errors());
}

Output (Valid Input): 
Array ( [number] => 8 )

Output (Invalid Input, odd number):
Array ( [number] => Array ( [0] => number validation failed. ) )

-------------- Example 3: Default Values for Missing Fields --------------

$data = ['name' => 'John'];
$validate = new Validate($data);
$validate->setDefaults([
    'age' => 25,
    'country' => 'USA'
]);

$validate->setRules([
    'name' => 'required|string',
    'age' => 'integer|min:18|max:100',
    'country' => 'string'
]);

if ($validate->validate()) {
    print_r($validate->sanitized());
} else {
    print_r($validate->errors());
}

Output:
Array ( [name] => John [age] => 25 [country] => USA )

------------------------- Example 4: Sanitization ------------------------

$data = [
    'username' => '   John Doe   ',
    'bio' => '<script>alert("XSS")</script>'
];

$validate = new Validate($data);
$validate->setRules([
    'username' => 'required|string|min:3|max:50',
    'bio' => 'string'
]);

if ($validate->validate()) {
    print_r($validate->sanitized());
} else {
    print_r($validate->errors());
}

Output (Sanitized Input):
Array ( [username] => John Doe [bio] => &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt; )

-------------------- Example 5: Nested Data Validation -------------------

$data = [
    'address' => [
        'city' => 'New York',
        'zip' => '10001'
    ]
];

$validate = new Validate($data);
$validate->setRules([
    'address.city' => 'required|string|min:3|max:50',
    'address.zip' => 'required|integer|min:10000|max:99999'
]);

if ($validate->validate()) {
    print_r($validate->sanitized());
} else {
    print_r($validate->errors());
}

Output:
Array ( [address] => Array ( [city] => New York [zip] => 10001 ) )
 */
