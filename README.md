# Validate Class: Custom Request Validation and Data Handling

This class is a custom request validation and data handling class. Its primary function is to handle incoming user data, validate that data based on defined rules, provide meaningful error messages for any validation failures, and optionally sanitize the input data.

The class is designed to be flexible, allowing for:

- Standard and custom validation rules.
- Error handling with detailed validation failure messages.
- Input sanitization, such as trimming strings and cleaning data.

This class ensures that only validated and sanitized data is processed, providing a clean and reliable input handling system for your application.

## Available Validation Rules:

- **'required'** : Ensures the field is not empty.
- **'string'** : Ensures the field is a string.
- **'integer'** : Ensures the field is an integer.
- **'min'** : Ensures the field value is at least a specified value (numeric or string length).
- **'max'** : Ensures the field value does not exceed a specified value (numeric or string length).
- **'email'** : Ensures the field is a valid email address.
- **'boolean'** : Ensures the field is a boolean value (true or false).
- **'url'** : Ensures the field is a valid URL.
- **'alpha'** : Ensures the field contains only alphabetic characters (A-Z, a-z).
- **'alpha_dash'** : Ensures the field contains only alphanumeric characters, dashes, and underscores.
- **'numeric'** : Ensures the field is numeric (integer or float).
- **'equal'** : Ensures the field is equal to another field.
- **'in'** : Ensures the field value is one of the specified values (comma-separated).
- **'not_in'** : Ensures the field value is not one of the specified values (comma-separated).
- **'date'** : Ensures the field is a valid date (in 'Y-m-d' format).

Each rule checks a specific condition on the field and returns an error message if the condition is not met. Rules may also accept parameters (e.g., 'min', 'max', 'in', 'not_in') which provide additional validation criteria.

## Example Code:

```php
$validate = new Validate([
    'name' => 'John_Doe',
    'emails' => ['email' => 'john.doe@example.com', 'email_confirm' => 'john.doe@example.com'],
    'age' => 25,
    'active' => true,
    'website' => 'https://ideaglory.com',
    'birthdate' => '1999-12-31',
    'category' => 'technology',
    'status' => 'inactive',
    'first_name' => 'John',
    'last_name' => 'Doe',
]);

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
    'emails.email_confirm.required' => 'The email is mandatory.',
    'emails.email_confirm.email' => 'The email must be a valid email address.',
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
} else {
    print_r($validate->errors());
}
```

## Example Use Cases:

### Example 1: Basic String Validation

```php
$data = ['username' => 'JohnDoe'];
$validate = new Validate($data);
$validate->setRules([
    'username' => 'required|string|min:3|max:20'
]);

if ($validate->validate()) {
    print_r($validate->validated());
} else {
    print_r($validate->errors());
}
```

- **Output (Valid Input)**:
  ```php
  Array ( [username] => JohnDoe )
  ```
- **Output (Invalid Input)**:
  ```php
  Array ( [username] => Array ( [0] => username must be at least 3 characters. ) )
  ```

### Example 2: Integer Validation with Range

```php
$data = ['age' => 20];
$validate = new Validate($data);
$validate->setRules([
    'age' => 'required|integer|min:18|max:65'
]);

if ($validate->validate()) {
    print_r($validate->validated());
} else {
    print_r($validate->errors());
}
```

- **Output**:
  ```php
  Array ( [age] => 20 )
  ```

### Example 3: Email Validation

```php
$data = ['email' => 'example@example.com'];
$validate = new Validate($data);
$validate->setRules([
    'email' => 'required|email'
]);

if ($validate->validate()) {
    print_r($validate->validated());
} else {
    print_r($validate->errors());
}
```

- **Output (Valid Email)**:
  ```php
  Array ( [email] => example@example.com )
  ```
- **Output (Invalid Email)**:
  ```php
  Array ( [email] => Array ( [0] => email must be a valid email. ) )
  ```

### Example 4: Custom Validation Rule

```php
$data = ['number' => 8];
$validate = new Validate($data);
$validate->setRules([
    'number' => 'required|even'
]);

$validate->addCustomRule('even', function ($value) {
    return $value % 2 === 0;
});

if ($validate->validate()) {
    print_r($validate->validated());
} else {
    print_r($validate->errors());
}
```

- **Output (Valid Input)**:
  ```php
  Array ( [number] => 8 )
  ```
- **Output (Invalid Input, odd number)**:
  ```php
  Array ( [number] => Array ( [0] => number validation failed. ) )
  ```

### Example 5: Default Values for Missing Fields

```php
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
    print_r($validate->validated());
} else {
    print_r($validate->errors());
}
```

- **Output**:
  ```php
  Array ( [name] => John [age] => 25 [country] => USA )
  ```

### Example 6: Sanitization

```php
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
    print_r($validate->validated());
} else {
    print_r($validate->errors());
}
```

- **Output (Sanitized Input)**:
  ```php
  Array ( [username] => John Doe [bio] => &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt; )
  ```

### Example 7: Nested Data Validation

```php
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
    print_r($validate->validated());
} else {
    print_r($validate->errors());
}
```

- **Output**:
  ```php
  Array ( [address] => Array ( [city] => New York [zip] => 10001 ) )
  ```

## Conclusion

This class can handle most validation scenarios, including:

- Basic rules (required, string, integer, email, min, max)
- Custom rules with flexible logic
- Nested field validation using dot notation
- Default values for missing fields
- Input sanitization to trim and escape malicious input
- Customizable error messages

## Contributing

Feel free to fork this repository and submit pull requests for improvements or bug fixes.

## License

This project is licensed under the MIT License. See the LICENSE file for details.

## Author

Created by [IdeaGlory](https://ideaglory.com).
