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

    'accepted'             => 'The :attribute must be accepted.',
    'accepted_if'          => 'The :attribute must be accepted when :other is :value.',
    'active_url'           => 'The :attribute is not a valid URL.',
    'after'                => 'The :attribute must be a date after :date.',
    'after_or_equal'       => 'The :attribute must be a date after or equal to :date.',
    'alpha'                => 'The :attribute must only contain letters.',
    'alpha_dash'           => 'The :attribute must only contain letters, numbers, dashes and underscores.',
    'alpha_num'            => 'The :attribute must only contain letters and numbers.',
    'array'                => 'The :attribute must be an array.',
    'before'               => 'The :attribute must be a date before :date.',
    'before_or_equal'      => 'The :attribute must be a date before or equal to :date.',
    'between'              => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file'    => 'The :attribute must be between :min and :max kilobytes.',
        'string'  => 'The :attribute must be between :min and :max characters.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'boolean'              => 'The :attribute field must be true or false.',
    'confirmed'            => 'The :attribute confirmation does not match.',
    'current_password'     => 'The password is incorrect.',
    'date'                 => 'The :attribute is not a valid date.',
    'date_equals'          => 'The :attribute must be a date equal to :date.',
    'date_format'          => 'The :attribute does not match the format :format.',
    'declined'             => 'The :attribute must be declined.',
    'declined_if'          => 'The :attribute must be declined when :other is :value.',
    'different'            => 'The :attribute and :other must be different.',
    'digits'               => 'The :attribute must be :digits digits.',
    'digits_between'       => 'The :attribute must be between :min and :max digits.',
    'dimensions'           => 'The :attribute has invalid image dimensions.',
    'distinct'             => 'The :attribute field has a duplicate value.',
    'email'                => 'The :attribute must be a valid email address.',
    'ends_with'            => 'The :attribute must end with one of the following: :values.',
    'enum'                 => 'The selected :attribute is invalid.',
    'exists'               => 'The selected :attribute is invalid.',
    'file'                 => 'The :attribute must be a file.',
    'filled'               => 'The :attribute field must have a value.',
    'gt'                   => [
        'numeric' => 'The :attribute must be greater than :value.',
        'file'    => 'The :attribute must be greater than :value kilobytes.',
        'string'  => 'The :attribute must be greater than :value characters.',
        'array'   => 'The :attribute must have more than :value items.',
    ],
    'gte'                  => [
        'numeric' => 'The :attribute must be greater than or equal to :value.',
        'file'    => 'The :attribute must be greater than or equal to :value kilobytes.',
        'string'  => 'The :attribute must be greater than or equal to :value characters.',
        'array'   => 'The :attribute must have :value items or more.',
    ],
    'image'                => 'The :attribute must be an image.',
    'in'                   => 'The selected :attribute is invalid.',
    'in_array'             => 'The :attribute field does not exist in :other.',
    'integer'              => 'The :attribute must be an integer.',
    'ip'                   => 'The :attribute must be a valid IP address.',
    'ipv4'                 => 'The :attribute must be a valid IPv4 address.',
    'ipv6'                 => 'The :attribute must be a valid IPv6 address.',
    'json'                 => 'The :attribute must be a valid JSON string.',
    'lt'                   => [
        'numeric' => 'The :attribute must be less than :value.',
        'file'    => 'The :attribute must be less than :value kilobytes.',
        'string'  => 'The :attribute must be less than :value characters.',
        'array'   => 'The :attribute must have less than :value items.',
    ],
    'lte'                  => [
        'numeric' => 'The :attribute must be less than or equal to :value.',
        'file'    => 'The :attribute must be less than or equal to :value kilobytes.',
        'string'  => 'The :attribute must be less than or equal to :value characters.',
        'array'   => 'The :attribute must not have more than :value items.',
    ],
    'mac_address'          => 'The :attribute must be a valid MAC address.',
    'max'                  => [
        'numeric' => 'The :attribute must not be greater than :max.',
        'file'    => 'The :attribute must not be greater than :max kilobytes.',
        'string'  => 'The :attribute must not be greater than :max characters.',
        'array'   => 'The :attribute must not have more than :max items.',
    ],
    'mimes'                => 'The :attribute must be a file of type: :values.',
    'mimetypes'            => 'The :attribute must be a file of type: :values.',
    'min'                  => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'multiple_of'          => 'The :attribute must be a multiple of :value.',
    'not_in'               => 'The selected :attribute is invalid.',
    'not_regex'            => 'The :attribute format is invalid.',
    'numeric'              => 'The :attribute must be a number.',
    'password'             => 'The password is incorrect.',
    'present'              => 'The :attribute field must be present.',
    'prohibited'           => 'The :attribute field is prohibited.',
    'prohibited_if'        => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless'    => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits'            => 'The :attribute field prohibits :other from being present.',
    'regex'                => 'The :attribute format is invalid.',
    'required'             => 'The :attribute field is required.',
    'required_array_keys'  => 'The :attribute field must contain entries for: :values.',
    'required_if'          => 'The :attribute field is required when :other is :value.',
    'required_unless'      => 'The :attribute field is required unless :other is in :values.',
    'required_with'        => 'The :attribute field is required when :values is present.',
    'required_with_all'    => 'The :attribute field is required when :values are present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same'                 => 'The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'starts_with'          => 'The :attribute must start with one of the following: :values.',
    'string'               => 'The :attribute must be a string.',
    'timezone'             => 'The :attribute must be a valid timezone.',
    'unique'               => 'The :attribute has already been taken.',
    'uploaded'             => 'The :attribute failed to upload.',
    'url'                  => 'The :attribute must be a valid URL.',
    'uuid'                 => 'The :attribute must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
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

    'attributes' => [],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */
    'sum'                                       => 'The sum of percentage within a vocabulary must add up to 100.',
    'overall_sum'                               => 'The sum of percentage must not be more than 100.',
    'required_custom'                           => ':attribute is required when there are multiple codes.',
    'total'                                     => ':attribute should be 100 when there is only one :values.',
    'csv_required'                              => 'At row :number :attribute is required',
    'csv_unique'                                => 'At row :number :attribute should be unique',
    'csv_invalid'                               => 'At row :number :attribute is invalid',
    'csv_numeric'                               => 'At row :number :attribute should be numeric',
    'csv_unique_validation'                     => 'At row :number :attribute is invalid and must be unique.',
    'csv_among'                                 => 'At row :number at least one :type among :attribute is required.',
    'csv_only_one'                              => 'At row :number only one among :attribute is required.',
    'year_value_narrative_validation'           => ':year and :value is required if :narrative is not empty.',
    'year_narrative_validation'                 => ':year is required if :narrative is not empty.',
    'org_required'                              => 'At least one organisation name is required',
    'custom_unique'                             => ':attribute has already been taken.',
    'user_identifier_taken'                     => 'Sorry! this User Identifier is already taken',
    'enter_valid'                               => 'Please enter valid :attribute',
    'sector_validation'                         => 'Sector must be present either at Activity or in all Transactions level.',
    'sector_narrative'                          => '<a href=\'%s\' >Sector Narrative</a> is required when vocabulay is 98 or 99.',
    'transaction_sector_narrative'              => '<a href=\'%s\' >Transaction Sector Narrative</a> is required when vocabulay is 98 or 99.',
    'transaction_sector_validation'             => 'All Transactions must contain Sector element.',
    'sector_in_activity_and_transaction_remove' => 'You can only mention Sector either at Activity or in Transaction level(should be included in all transactions) but not both. <br/>Please click the link to remove Sector From: <a href=\'%s\' class=\'delete_data\'>Transaction Level</a> OR <a href=\'%s\' class=\'delete_data\'>Activity Level</a>',
    'sector_in_activity_and_transaction'        => 'You need to mention either Recipient Country or Region either in Activity Level or in Transaction level. You can\'t have Country/Region in both Activity level and Transaction level. Also, they cannot be empty for both activity and transactions',
    'recipient_country_or_region_required'      => 'Either Recipient Country or Recipient Region is required in Activity Level or Transaction Level.',
    'transaction_recipient_country_or_region_required' => 'All Transactions must contain Recipient Region or Recipient Country',
    'sum_of_percentage'                         => 'The sum of percentage in :attribute must be 100.',
    'validation_before_completed'               => 'Please make sure you enter the following fields before changing to completed state.',
    'reporting_org_identifier_unique'           => 'This reporting organization identifier is being used by :orgName. This identifier has to be unique. Please contact us at support@aidstream.org',
    'code_list'                                 => ':attribute is not valid.',
    'string'                                    => ':attribute should be string',
    'negative'                                  => ':attribute cannot be negative',
    'actual_date'                               => 'Actual Start Date And Actual End Date must not exceed present date',
    'multiple_activity_date'                    => 'Multiple Activity dates are not allowed.',
    'start_end_date'                            => 'Actual Start Date or Planned Start Date should be before Actual End Date or Planned End Date.',
    'csv_date'                                  => ':attribute must be of format Y-m-d.',
    'multiple_values'                           => 'Multiple :attribute are not allowed.',
    'csv_size'                                  => 'At least one :attribute is required',
    'multiple_narratives'                       => 'Multiple narratives for :attribute with the same type is not allowed.',
    'required_only_one_among'                   => 'Either :attribute or :values is required.',
    'recipient_country_region_percentage_sum'   => 'Sum of percentage of Recipient Country and Recipient Region must be equal to 100.',
    'invalid_in_transaction'                    => 'Entered :attribute is incorrect in Transaction.',
    'invalid_in_sector'                         => 'Entered :attribute is incorrect in Sector.',
    'required_if_in_transaction'                => ':attribute is required if :values is not present in Transaction.',
    'sector_vocabulary_required'                => 'Sector Vocabulary is required in Transaction if not present in Activity Level.',
    'required_in_transaction'                   => ':attribute is required in Transaction.',
    'invalid_language'                          => 'Invalid :attribute language',
    'unique_lang'                               => 'Repeated :attribute in the same language is not allowed.',
    'indicator_ascending'                       => 'Indicator Ascending should be true/false, 0/1 or Yes/No.',
    'indicator_size'                            => 'Indicator Baseline Year or Value should occur once and no more than once within an Indicator.',
    'narrative_required'                        => ':attribute Narrative is required.',
    'no_more_than_once'                         => ':attribute should occur once and no more than once within :values.',
    'budget_period_end_date'                    => 'Budget Period End Date',
    'spaces_not_allowed'                        => 'You cannot enter spaces in organization name abbreviation.',
    'custom'                                    => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],
    'not_in_spam_emails' => 'This email has been flagged as spam and cannot be used.',
    'no_leading_white_space' => 'The activity-identifier must not start with space.',
    'no_spaces_in_between' => 'The activity-identifier must not contain spaces.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes'    => [],
    'within_a_year' => "The :attribute must be within a year after :date.",
    'required_if_any' => "The :field is required if any of the fields of :element are filled.",
    'amount' => 'Please enter amount in the format xx.xx',
    'greater' => 'The :attribute must be greater than or equal to :value',
    'date_greater_than' => 'The date must be greater than :value',
];
