<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AzureStoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // === REQUIRED FIELDS ===
            'name' => [
                'required',
                'string',
                'max:100',
                'min:2',
                'regex:/^[a-zA-Z\s\-\.]+$/'
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    $exists = DB::connection('sqlsrv')
                        ->table('Azure_Users')
                        ->where('mail', strtolower(trim($value)))
                        ->exists();
                    
                    if ($exists) {
                        $fail('This email address is already registered in Azure.');
                    }
                }
            ],
            'employee_id' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9\-]+$/',
                function ($attribute, $value, $fail) {
                    $exists = DB::connection('sqlsrv')
                        ->table('Azure_Users')
                        ->where('EmployeCode', strtoupper(trim($value)))
                        ->exists();
                    
                    if ($exists) {
                        $fail('This employee ID is already taken.');
                    }
                }
            ],
            'phone' => [
                'required',
                'string',
                'max:30',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/'
            ],

            // === OPTIONAL FIELDS ===
            'mobile_phone' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/'
            ],
            'job_title' => [
                'nullable',
                'string',
                'max:255'
            ],
            'department' => [
                'nullable',
                'string',
                'max:255'
            ],
            'company_id' => [
                'nullable',
                'string',
                'max:255'
            ],

            // === MODULE ASSIGNMENTS (NEW JSON FORMAT) ===
            'modules_json' => [
                'required',
                'json'
            ],

            // === OLD FORMAT (KEEP FOR BACKWARDS COMPATIBILITY) ===
            'modules' => [
                'sometimes',
                'array',
                'min:1'
            ],
            'modules.*.module_id' => [
                'required_with:modules',
                'uuid',
                function ($attribute, $value, $fail) {
                    $exists = DB::connection('sqlsrv')
                        ->table('Azure_SoftwareMaster')
                        ->where('Id', $value)
                        ->where('IsActive', 1)
                        ->where('IsDeleted', 0)
                        ->exists();
                    
                    if (!$exists) {
                        $fail('The selected module is invalid or inactive.');
                    }
                }
            ],
            'modules.*.application_id' => [
                'required_with:modules',
                'string',
                'max:255'
            ],
            'modules.*.application_name' => [
                'required_with:modules',
                'string',
                'max:255'
            ],
            'modules.*.role_id' => [
                'required_with:modules',
                'uuid'
            ],
            'modules.*.role_code' => [
                'nullable',
                'string',
                'max:100'
            ],
            'modules.*.location_code' => [
                'required_with:modules',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    if (strtoupper($value) === 'ALL') {
                        return;
                    }
                    
                    $exists = DB::connection('sqlsrv')
                        ->table('Cust_Office')
                        ->where('code', strtoupper($value))
                        ->where('IsActive', 1)
                        ->where('IsDeleted', 0)
                        ->exists();
                    
                    if (!$exists) {
                        $fail('The selected location is invalid or inactive.');
                    }
                }
            ],
            'modules.*.location_id' => [
                'nullable',
                'uuid'
            ],
            'modules.*.function_code' => [
                'nullable',
                'string',
                'max:100'
            ],
            'modules.*.function_id' => [
                'nullable',
                'uuid'
            ]
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Name validation messages
            'name.required' => 'The full name is required.',
            'name.min' => 'The name must be at least 2 characters.',
            'name.max' => 'The name cannot exceed 100 characters.',
            'name.regex' => 'The name may only contain letters, spaces, hyphens, and dots.',

            // Email validation messages
            'email.required' => 'The email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'The email cannot exceed 255 characters.',

            // Employee ID validation messages
            'employee_id.required' => 'The employee ID is required.',
            'employee_id.max' => 'The employee ID cannot exceed 50 characters.',
            'employee_id.regex' => 'Employee ID must contain only uppercase letters, numbers, and hyphens.',

            // Phone validation messages
            'phone.required' => 'The phone number is required.',
            'phone.max' => 'The phone number cannot exceed 30 characters.',
            'phone.regex' => 'Please enter a valid phone number format.',

            // Module JSON validation messages
            'modules_json.required' => 'Please assign at least one module to this user.',
            'modules_json.json' => 'Invalid module data format.',

            // Old format messages (backwards compatibility)
            'modules.required' => 'Please assign at least one module to this user.',
            'modules.array' => 'Module data must be in a valid format.',
            'modules.min' => 'At least one module must be assigned.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $data = [
            'name' => trim($this->name ?? ''),
            'email' => strtolower(trim($this->email ?? '')),
            'employee_id' => strtoupper(trim($this->employee_id ?? '')),
            'phone' => $this->sanitizePhone($this->phone ?? ''),
        ];

        // Sanitize optional fields
        if ($this->has('mobile_phone')) {
            $data['mobile_phone'] = $this->sanitizePhone($this->mobile_phone);
        }
        if ($this->has('job_title')) {
            $data['job_title'] = trim($this->job_title);
        }
        if ($this->has('department')) {
            $data['department'] = trim($this->department);
        }
        if ($this->has('company_id')) {
            $data['company_id'] = trim($this->company_id);
        }

        // Keep modules_json as-is (don't decode it here, let controller handle it)
        if ($this->has('modules_json')) {
            $data['modules_json'] = $this->modules_json;
        }

        // Sanitize old format modules array (backwards compatibility)
        if ($this->has('modules') && is_array($this->modules)) {
            $data['modules'] = array_map(function ($module) {
                return [
                    'module_id' => trim($module['module_id'] ?? ''),
                    'application_id' => trim($module['application_id'] ?? ''),
                    'application_name' => trim($module['application_name'] ?? ''),
                    'role_id' => trim($module['role_id'] ?? ''),
                    'role_code' => isset($module['role_code']) ? trim($module['role_code']) : null,
                    'location_code' => isset($module['location_code']) ? strtoupper(trim($module['location_code'])) : null,
                    'location_id' => isset($module['location_id']) ? trim($module['location_id']) : null,
                    'function_code' => isset($module['function_code']) ? trim($module['function_code']) : null,
                    'function_id' => isset($module['function_id']) ? trim($module['function_id']) : null,
                ];
            }, $this->modules);
        }

        $this->merge($data);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate modules_json if present
            if ($this->has('modules_json')) {
                $modulesJson = $this->input('modules_json');
                
                // Try to decode JSON
                $modules = json_decode($modulesJson, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $validator->errors()->add('modules_json', 'Invalid JSON format: ' . json_last_error_msg());
                    return;
                }
                
                // Ensure at least one module
                if (empty($modules) || !is_array($modules)) {
                    $validator->errors()->add('modules_json', 'At least one module assignment is required.');
                    return;
                }
                
                // Validate structure of decoded JSON
                foreach ($modules as $index => $module) {
                    if (!is_array($module)) {
                        $validator->errors()->add('modules_json', "Module #{$index} must be an object.");
                        continue;
                    }
                    
                    // Check required fields
                    $requiredFields = ['module_id', 'application_id', 'application_name'];
                    foreach ($requiredFields as $field) {
                        if (empty($module[$field])) {
                            $validator->errors()->add(
                                'modules_json',
                                "Module #{$index}: {$field} is required."
                            );
                        }
                    }
                    
                    // Validate module_id is valid UUID and exists in database
                    if (!empty($module['module_id'])) {
                        if (!$this->isValidUuid($module['module_id'])) {
                            $validator->errors()->add(
                                'modules_json',
                                "Module #{$index}: Invalid module ID format."
                            );
                        } else {
                            // Check if module exists and is active
                            $moduleExists = DB::connection('sqlsrv')
                                ->table('Azure_SoftwareMaster')
                                ->where('Id', $module['module_id'])
                                ->where('IsActive', 1)
                                ->where('IsDeleted', 0)
                                ->exists();
                            
                            if (!$moduleExists) {
                                $validator->errors()->add(
                                    'modules_json',
                                    "Module #{$index}: Selected module does not exist or is inactive."
                                );
                            }
                        }
                    }
                    
                    // Validate locationCodes array exists and has at least one item
                    if (empty($module['locationCodes']) || !is_array($module['locationCodes'])) {
                        $validator->errors()->add(
                            'modules_json',
                            "Module #{$index}: At least one location must be selected."
                        );
                    } else {
                        // Validate each location code (skip 'ALL')
                        foreach ($module['locationCodes'] as $locationCode) {
                            if (strtoupper($locationCode) === 'ALL') {
                                continue; // 'ALL' is valid
                            }
                            
                            $locationExists = DB::connection('sqlsrv')
                                ->table('Cust_Office')
                                ->where('code', strtoupper($locationCode))
                                ->where('IsActive', 1)
                                ->where('IsDeleted', 0)
                                ->exists();
                            
                            if (!$locationExists) {
                                $validator->errors()->add(
                                    'modules_json',
                                    "Module #{$index}: Location code '{$locationCode}' is invalid or inactive."
                                );
                            }
                        }
                    }
                    
                    // Validate roleIds array exists and has at least one item
                    if (empty($module['roleIds']) || !is_array($module['roleIds'])) {
                        $validator->errors()->add(
                            'modules_json',
                            "Module #{$index}: At least one role must be selected."
                        );
                    } else {
                        // Validate each role ID is a valid UUID (skip 'ALL')
                        foreach ($module['roleIds'] as $roleId) {
                            if (strtoupper($roleId) === 'ALL') {
                                continue; // 'ALL' is valid
                            }
                            
                            if (!$this->isValidUuid($roleId)) {
                                $validator->errors()->add(
                                    'modules_json',
                                    "Module #{$index}: Invalid role ID format '{$roleId}'."
                                );
                            }
                        }
                    }
                    
                    // For SCM modules (OPS, OPSUAT, OPS UAT), validate functions if present
                    $isSCM = in_array(strtoupper($module['application_name'] ?? ''), ['OPS', 'OPS UAT', 'OPSUAT']);
                    if ($isSCM) {
                        if (!empty($module['functionIds']) && is_array($module['functionIds'])) {
                            foreach ($module['functionIds'] as $functionId) {
                                if (strtoupper($functionId) === 'ALL') {
                                    continue; // 'ALL' is valid
                                }
                                
                                if (!$this->isValidUuid($functionId)) {
                                    $validator->errors()->add(
                                        'modules_json',
                                        "Module #{$index}: Invalid function ID format '{$functionId}'."
                                    );
                                }
                            }
                        }
                    }
                }
                
                return; // Skip old format validation if modules_json is present
            }

            // Old format validation (backwards compatibility)
            $modules = $this->input('modules', []);

            if (empty($modules)) {
                $validator->errors()->add('modules', 'At least one module assignment is required.');
                return;
            }

            foreach ($modules as $index => $module) {
                $requiredFields = ['module_id', 'application_id', 'application_name', 'role_id', 'location_code'];
                
                foreach ($requiredFields as $field) {
                    if (empty($module[$field])) {
                        $fieldName = ucwords(str_replace('_', ' ', $field));
                        $validator->errors()->add(
                            "modules.{$index}.{$field}",
                            "{$fieldName} is required for module assignment #" . ($index + 1) . "."
                        );
                    }
                }

                $uuidFields = ['module_id', 'role_id'];
                foreach ($uuidFields as $field) {
                    if (!empty($module[$field]) && !$this->isValidUuid($module[$field])) {
                        $validator->errors()->add(
                            "modules.{$index}.{$field}",
                            "Invalid GUID format for {$field} in module assignment #" . ($index + 1) . "."
                        );
                    }
                }
            }

            // Check for duplicates
            $seen = [];
            foreach ($modules as $index => $module) {
                $key = ($module['module_id'] ?? '') . '|' . ($module['location_code'] ?? '');
                
                if (in_array($key, $seen)) {
                    $validator->errors()->add(
                        "modules.{$index}",
                        "Duplicate module assignment detected."
                    );
                }
                
                $seen[] = $key;
            }
        });
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'full name',
            'email' => 'email address',
            'employee_id' => 'employee ID',
            'phone' => 'phone number',
            'mobile_phone' => 'mobile phone',
            'job_title' => 'job title',
            'department' => 'department',
            'company_id' => 'company',
            'modules' => 'module assignments',
            'modules_json' => 'module assignments',
        ];
    }

    /**
     * Sanitize phone number
     */
    private function sanitizePhone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        return preg_replace('/[^0-9\+\-\(\)\s]/', '', $phone);
    }

    /**
     * Check if a string is a valid UUID/GUID
     */
    private function isValidUuid(string $uuid): bool
    {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
        return preg_match($pattern, $uuid) === 1;
    }
}