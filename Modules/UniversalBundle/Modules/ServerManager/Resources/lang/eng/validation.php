<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'The :attribute must be accepted.',
    'accepted_if' => 'The :attribute must be accepted when :other is :value.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'alpha' => 'The :attribute must only contain letters.',
    'alpha_dash' => 'The :attribute must only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'The :attribute must only contain letters and numbers.',
    'array' => 'The :attribute must be an array.',
    'ascii' => 'The :attribute must only contain single-byte alphanumeric characters and symbols.',
    'before' => 'The :attribute must be a date before :date.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'between' => [
        'array' => 'The :attribute must have between :min and :max items.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'numeric' => 'The :attribute must be between :min and :max.',
        'string' => 'The :attribute must be between :min and :max characters.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'can' => 'The :attribute field contains an unauthorized value.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'current_password' => 'The password is incorrect.',
    'date' => 'The :attribute is not a valid date.',
    'date_equals' => 'The :attribute must be a date equal to :date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'decimal' => 'The :attribute must have :decimal decimal places.',
    'declined' => 'The :attribute must be declined.',
    'declined_if' => 'The :attribute must be declined when :other is :value.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'doesnt_end_with' => 'The :attribute may not end with one of the following: :values.',
    'doesnt_start_with' => 'The :attribute may not start with one of the following: :values.',
    'email' => 'The :attribute must be a valid email address.',
    'ends_with' => 'The :attribute must end with one of the following: :values.',
    'enum' => 'The selected :attribute is invalid.',
    'exists' => 'The selected :attribute is invalid.',
    'extensions' => 'The :attribute must have one of the following extensions: :values.',
    'file' => 'The :attribute must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'array' => 'The :attribute must have more than :value items.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'numeric' => 'The :attribute must be greater than :value.',
        'string' => 'The :attribute must be greater than :value characters.',
    ],
    'gte' => [
        'array' => 'The :attribute must have :value items or more.',
        'file' => 'The :attribute must be greater than or equal to :value kilobytes.',
        'numeric' => 'The :attribute must be greater than or equal to :value.',
        'string' => 'The :attribute must be greater than or equal to :value characters.',
    ],
    'hex_color' => 'The :attribute field must be a valid hexadecimal color.',
    'image' => 'The :attribute must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer' => 'The :attribute must be an integer.',
    'ip' => 'The :attribute must be a valid IP address.',
    'ipv4' => 'The :attribute must be a valid IPv4 address.',
    'ipv6' => 'The :attribute must be a valid IPv6 address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'lowercase' => 'The :attribute must be lowercase.',
    'lt' => [
        'array' => 'The :attribute must have less than :value items.',
        'file' => 'The :attribute must be less than :value kilobytes.',
        'numeric' => 'The :attribute must be less than :value.',
        'string' => 'The :attribute must be less than :value characters.',
    ],
    'lte' => [
        'array' => 'The :attribute must not have more than :value items.',
        'file' => 'The :attribute must be less than or equal to :value kilobytes.',
        'numeric' => 'The :attribute must be less than or equal to :value.',
        'string' => 'The :attribute must be less than or equal to :value characters.',
    ],
    'mac_address' => 'The :attribute must be a valid MAC address.',
    'max' => [
        'array' => 'The :attribute must not have more than :max items.',
        'file' => 'The :attribute must not be greater than :max kilobytes.',
        'numeric' => 'The :attribute must not be greater than :max.',
        'string' => 'The :attribute must not be greater than :max characters.',
    ],
    'max_digits' => 'The :attribute must not have more than :max digits.',
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'array' => 'The :attribute must have at least :min items.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'numeric' => 'The :attribute must be at least :min.',
        'string' => 'The :attribute must be at least :min characters.',
    ],
    'min_digits' => 'The :attribute must have at least :min digits.',
    'missing' => 'The :attribute field is missing.',
    'missing_if' => 'The :attribute field is missing when :other is :value.',
    'missing_unless' => 'The :attribute field is missing unless :other is :value.',
    'missing_with' => 'The :attribute field is missing when :values is present.',
    'missing_with_all' => 'The :attribute field is missing when :values are present.',
    'multiple_of' => 'The :attribute must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'password' => [
        'letters' => 'The :attribute must contain at least one letter.',
        'mixed' => 'The :attribute must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute must contain at least one number.',
        'symbols' => 'The :attribute must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data leak. Please choose a different :attribute.',
    ],
    'present' => 'The :attribute field must be present.',
    'present_if' => 'The :attribute field must be present when :other is :value.',
    'present_unless' => 'The :attribute field must be present unless :other is :value.',
    'present_with' => 'The :attribute field must be present when :values is present.',
    'present_with_all' => 'The :attribute field must be present when :values are present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_array_keys' => 'The :attribute field must contain entries for: :values.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_if_accepted' => 'The :attribute field is required when :other is accepted.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'array' => 'The :attribute must contain :size items.',
        'file' => 'The :attribute must be :size kilobytes.',
        'numeric' => 'The :attribute must be :size.',
        'string' => 'The :attribute must be :size characters.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid timezone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'uppercase' => 'The :attribute must be uppercase.',
    'url' => 'The :attribute must be a valid URL.',
    'ulid' => 'The :attribute must be a valid ULID.',
    'uuid' => 'The :attribute must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "rule.attribute" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'domain_name' => [
            'required' => 'Domain name is required.',
            'max' => 'Domain name cannot be more than 255 characters.',
        ],
        'domain_provider' => [
            'required' => 'Domain provider is required.',
            'max' => 'Domain provider cannot be more than 255 characters.',
        ],
        'domain_type' => [
            'required' => 'Domain type is required.',
            'max' => 'Domain type cannot be more than 10 characters.',
        ],
        'registrar' => [
            'max' => 'Registrar cannot be more than 255 characters.',
        ],
        'registrar_url' => [
            'url' => 'Registrar URL must be a valid URL.',
            'max' => 'Registrar URL cannot be more than 255 characters.',
        ],
        'registration_date' => [
            'required' => 'Registration date is required.',
            'date' => 'Registration date must be a valid date.',
        ],
        'expiry_date' => [
            'required' => 'Expiry date is required.',
            'date' => 'Expiry date must be a valid date.',
            'after' => 'Expiry date must be after registration date.',
        ],
        'renewal_date' => [
            'date' => 'Expiry date must be a valid date.',
            'after' => 'Expiry date must be after registration date.',
        ],
        'annual_cost' => [
            'numeric' => 'Annual cost must be a number.',
            'min' => 'Annual cost cannot be less than 0.',
            'max' => 'Annual cost cannot be more than 999,999.99.',
        ],
        'billing_cycle' => [
            'max' => 'Billing cycle cannot be more than 50 characters.',
        ],
        'status' => [
            'required' => 'Status is required.',
            'in' => 'Status must be one of: active, expired, suspended, transferred, pending.',
        ],
        'dns_provider' => [
            'max' => 'DNS provider cannot be more than 255 characters.',
        ],
        'nameservers' => [
            'array' => 'Nameservers must be an array.',
        ],
        'nameservers.*' => [
            'string' => 'Nameserver must be a string.',
            'max' => 'Nameserver cannot be more than 255 characters.',
        ],
        'dns_records' => [
            'array' => 'DNS records must be an array.',
        ],
        'dns_records.*.type' => [
            'required_with' => 'DNS record type is required.',
        ],
        'dns_records.*.name' => [
            'required_with' => 'DNS record name is required.',
        ],
        'dns_records.*.value' => [
            'required_with' => 'DNS record value is required.',
        ],
        'hosting_id' => [
            'exists' => 'Selected hosting not found.',
        ],
        'assigned_to' => [
            'exists' => 'Selected user not found.',
        ],
        'notes' => [
            'max' => 'Notes cannot be more than 1000 characters.',
        ],

        // Hosting validation messages
        'name' => [
            'required' => 'Hosting name is required.',
            'max' => 'Hosting name cannot be more than 255 characters.',
        ],
        'hosting_provider' => [
            'required' => 'Hosting provider is required.',
            'max' => 'Hosting provider cannot be more than 255 characters.',
        ],
        'server_type' => [
            'required' => 'Server type is required.',
            'in' => 'Server type must be one of: shared, vps, dedicated, cloud.',
        ],
        'server_location' => [
            'max' => 'Server location cannot be more than 255 characters.',
        ],
        'ip_address' => [
            'ip' => 'IP address must be a valid IP address.',
        ],
        'purchase_date' => [
            'required' => 'Purchase date is required.',
            'date' => 'Purchase date must be a valid date.',
        ],
        'renewal_date' => [
            'required' => 'Expiry date is required.',
            'date' => 'Expiry date must be a valid date.',
            'after' => 'Expiry date must be after purchase date.',
        ],
        'disk_space' => [
            'max' => 'Disk space cannot be more than 100 characters.',
        ],
        'bandwidth' => [
            'max' => 'Bandwidth cannot be more than 100 characters.',
        ],
        'control_panel' => [
            'max' => 'Control panel cannot be more than 100 characters.',
        ],
        'ftp_username' => [
            'max' => 'FTP username cannot be more than 255 characters.',
        ],
        'ftp_password' => [
            'max' => 'FTP password cannot be more than 255 characters.',
        ],
        'database_limit' => [
            'integer' => 'Database limit must be an integer.',
            'min' => 'Database limit cannot be less than 0.',
        ],
        'email_limit' => [
            'integer' => 'Email limit must be an integer.',
            'min' => 'Email limit cannot be less than 0.',
        ],
    ],

    'provider' => [
        'name' => [
            'required' => 'Provider name is required.',
            'string' => 'Provider name must be a string.',
            'max' => 'Provider name cannot be more than 255 characters.',
        ],
        'url' => [
            'url' => 'Provider URL must be a valid URL.',
            'max' => 'Provider URL cannot be more than 255 characters.',
        ],
        'type' => [
            'required' => 'Provider type is required.',
            'in' => 'Provider type must be domain, hosting, or both.',
        ],
        'status' => [
            'required' => 'Provider status is required.',
            'in' => 'Provider status must be active or inactive.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'domain_name' => 'Domain Name',
        'domain_provider' => 'Domain Provider',
        'provider_url' => 'Provider URL',
        'domain_type' => 'Domain Type',
        'registrar' => 'Registrar',
        'registrar_url' => 'Registrar URL',
        'registrar_username' => 'Registrar Username',
        'registrar_password' => 'Registrar Password',
        'registrar_status' => 'Registrar Status',
        'registration_date' => 'Registration Date',
        'expiry_date' => 'Expiry Date',
        'renewal_date' => 'Expiry Date',
        'username' => 'Username',
        'password' => 'Password',
        'annual_cost' => 'Annual Cost',
        'billing_cycle' => 'Billing Cycle',
        'status' => 'Status',
        'hosting_id' => 'Hosting',
        'project_id' => 'Project',
        'client_id' => 'Client',
        'dns_provider' => 'DNS Provider',
        'dns_status' => 'DNS Status',
        'nameservers' => 'Nameservers',
        'dns_records' => 'DNS Records',
        'whois_protection' => 'WHOIS Protection',
        'auto_renewal' => 'Auto Renewal',
        'expiry_notification' => 'Expiry Notification',
        'notification_days_before' => 'Notification Days Before',
        'notification_time_unit' => 'Notification Time Unit',
        'assigned_to' => 'Assigned To',
        'notes' => 'Notes',

        // Hosting attributes
        'name' => 'Name',
        'hosting_provider' => 'Hosting Provider',
        'server_type' => 'Server Type',
        'server_location' => 'Server Location',
        'ip_address' => 'IP Address',
        'purchase_date' => 'Purchase Date',
        'disk_space' => 'Disk Space',
        'bandwidth' => 'Bandwidth',
        'ssl_certificate' => 'SSL Certificate',
        'backup_enabled' => 'Backup Enabled',
        'control_panel' => 'Control Panel',
        'cpanel_url' => 'CPanel URL',
        'username' => 'Username',
        'password' => 'Password',
        'project' => 'Project',
        'client' => 'Client',
        'ftp_username' => 'FTP Username',
        'ftp_password' => 'FTP Password',
        'database_limit' => 'Database Limit',
        'email_limit' => 'Email Limit',

        // Provider attributes
        'provider_name' => 'Provider Name',
        'provider_type' => 'Provider Type',
        'provider_description' => 'Provider Description',
    ],
];
