<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AzureStoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * Note: Actual authorization is handled by middleware/policies
     */
    public function authorize(): bool
    {
        // Only authenticated users with proper permissions can create Azure users
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
                'regex:/^[a-zA-Z\s\-\.]+$/' // Allow letters, spaces, hyphens, dots
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    // Check if email exists in Azure_Users table
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
                'regex:/^[A-Z0-9\-]+$/', // Uppercase letters, numbers, hyphens
                function ($attribute, $value, $fail) {
                    // Check if employee ID exists in Azure_Users table
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

            // === MODULE ASSIGNMENTS (REQUIRED) ===
            'modules' => [
                'required',
                'array',
                'min:1'
            ],
            'modules.*.module_id' => [
                'required',
                'uuid', // GUID format
                function ($attribute, $value, $fail) {
                    // Verify module exists in Azure_SoftwareMaster
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
                'required',
                'string', // Can be GUID or other format
                'max:255'
            ],
            'modules.*.application_name' => [
                'required',
                'string',
                'max:255'
            ],
            'modules.*.role_id' => [
                'required',
                'uuid' // GUID format
            ],
            'modules.*.role_code' => [
                'nullable',
                'string',
                'max:100'
            ],
            'modules.*.location_code' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    // Allow 'ALL' or verify location exists in Cust_Office
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
                'uuid' // GUID format, optional
            ],
            'modules.*.function_code' => [
                'nullable',
                'string',
                'max:100' // For SCM modules
            ],
            'modules.*.function_id' => [
                'nullable',
                'uuid' // For SCM modules
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

            // Mobile phone validation messages
            'mobile_phone.max' => 'The mobile phone cannot exceed 30 characters.',
            'mobile_phone.regex' => 'Please enter a valid mobile phone format.',

            // Job title validation messages
            'job_title.max' => 'The job title cannot exceed 255 characters.',

            // Department validation messages
            'department.max' => 'The department cannot exceed 255 characters.',

            // Company validation messages
            'company_id.max' => 'The company name cannot exceed 255 characters.',

            // Module validation messages
            'modules.required' => 'Please assign at least one module to this user.',
            'modules.array' => 'Module data must be in a valid format.',
            'modules.min' => 'At least one module must be assigned.',

            // Module field validation messages
            'modules.*.module_id.required' => 'Module ID is required for each assignment.',
            'modules.*.module_id.uuid' => 'Module ID must be a valid GUID format.',

            'modules.*.application_id.required' => 'Application ID is required for each module.',
            'modules.*.application_id.max' => 'Application ID cannot exceed 255 characters.',

            'modules.*.application_name.required' => 'Application name is required for each module.',
            'modules.*.application_name.max' => 'Application name cannot exceed 255 characters.',

            'modules.*.role_id.required' => 'Role ID is required for each module assignment.',
            'modules.*.role_id.uuid' => 'Role ID must be a valid GUID format.',

            'modules.*.role_code.max' => 'Role code cannot exceed 100 characters.',

            'modules.*.location_code.required' => 'Location code is required for each module assignment.',
            'modules.*.location_code.max' => 'Location code cannot exceed 50 characters.',

            'modules.*.location_id.uuid' => 'Location ID must be a valid GUID format.',

            'modules.*.function_code.max' => 'Function code cannot exceed 100 characters.',
            'modules.*.function_id.uuid' => 'Function ID must be a valid GUID format.'
        ];
    }

    /**
     * Prepare the data for validation.
     * Sanitize and normalize input before validation runs.
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

        // Sanitize modules array
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
     * Add additional validation logic after initial validation passes.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $modules = $this->input('modules', []);

            // Ensure at least one module is provided
            if (empty($modules)) {
                $validator->errors()->add('modules', 'At least one module assignment is required.');
                return;
            }

            // Validate each module has all required fields
            foreach ($modules as $index => $module) {
                // Check for required fields
                $requiredFields = ['module_id', 'application_id', 'application_name', 'role_id', 'location_code'];
                
                foreach ($requiredFields as $field) {
                    if (empty($module[$field])) {
                        $fieldName = ucwords(str_replace('_', ' ', $field));
                        $validator->errors()->add(
                            "modules.{$index}.{$field}",
                            "{$fieldName} is required for module assignment #{" . ($index + 1) . "}."
                        );
                    }
                }

                // Validate GUID format for UUID fields
                $uuidFields = ['module_id', 'role_id'];
                foreach ($uuidFields as $field) {
                    if (!empty($module[$field]) && !$this->isValidUuid($module[$field])) {
                        $validator->errors()->add(
                            "modules.{$index}.{$field}",
                            "Invalid GUID format for {$field} in module assignment #{" . ($index + 1) . "}."
                        );
                    }
                }
            }

            // Check for duplicate module assignments (same module + location)
            $seen = [];
            foreach ($modules as $index => $module) {
                $key = ($module['module_id'] ?? '') . '|' . ($module['location_code'] ?? '');
                
                if (in_array($key, $seen)) {
                    $validator->errors()->add(
                        "modules.{$index}",
                        "Duplicate module assignment detected. Each module can only be assigned once per location."
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
        ];
    }

    /**
     * Sanitize phone number - remove invalid characters but keep valid ones
     */
    private function sanitizePhone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Keep only: digits, +, -, (, ), and spaces
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