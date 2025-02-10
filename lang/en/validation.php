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

    'after'                => 'The :attribute must be a date after :date.',
    'after_or_equal'       => 'The :attribute must be a date after or equal to :date.',
    'before'               => 'The :attribute must be a date before :date.',
    'before_or_equal'      => 'The :attribute must be a date before or equal to :date.',
    'between'              => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file'    => 'The :attribute must be between :min and :max kilobytes.',
        'string'  => 'The :attribute must be between :min and :max characters.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'confirmed'            => 'The :attribute confirmation does not match.',
    'current_password'     => 'The password is incorrect.',
    'date'                 => 'The :attribute is not a valid date.',
    'date_format'          => 'The :attribute does not match the format :format.',
    'digits'               => 'The :attribute must be :digits digits.',
    'email'                => 'The :attribute must be a valid email address.',
    'exists'               => 'The selected :attribute is invalid.',
    'gte'                  => [
        'numeric' => 'The :attribute must be greater than or equal to :value.',
        'file'    => 'The :attribute must be greater than or equal to :value kilobytes.',
        'string'  => 'The :attribute must be greater than or equal to :value characters.',
        'array'   => 'The :attribute must have :value items or more.',
    ],
    'in'                   => 'The selected :attribute is invalid.',
    'integer'              => 'The :attribute must be an integer.',
    'lte'                  => [
        'numeric' => 'The :attribute must be less than or equal to :value.',
        'file'    => 'The :attribute must be less than or equal to :value kilobytes.',
        'string'  => 'The :attribute must be less than or equal to :value characters.',
        'array'   => 'The :attribute must not have more than :value items.',
    ],
    'max'                  => [
        'numeric' => 'The :attribute must not be greater than :max.',
        'file'    => 'The :attribute must not be greater than :max kilobytes.',
        'string'  => 'The :attribute must not be greater than :max characters.',
        'array'   => 'The :attribute must not have more than :max items.',
    ],
    'min'                  => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'not_regex'            => 'The :attribute format is invalid.',
    'numeric'              => 'The :attribute must be a number.',
    'password'             => 'The password is incorrect.',
    'regex'                => 'The :attribute format is invalid.',
    'required'             => 'The :attribute field is required.',
    'required_with'        => 'The :attribute field is required when :values is present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'size'                 => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'unique'               => 'The :attribute has already been taken.',
    'url'                  => 'The :attribute must be a valid URL.',


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
    'sum'                                              => 'The sum of percentage within a vocabulary must add up to 100.',
    'total'                                            => ':attribute should be 100 when there is only one :values.',
    'year_value_narrative_validation'                  => ':year and :value is required if :narrative is not empty.',
    'code_list'                                        => ':attribute is not valid.',
    'string'                                           => ':attribute should be string',
    'negative'                                         => ':attribute cannot be negative',
    'actual_date'                                      => 'Actual Start Date And Actual End Date must not exceed present date',
    'multiple_activity_date'                           => 'Multiple Activity dates are not allowed.',
    'start_end_date'                                   => 'Actual Start Date or Planned Start Date should be before Actual End Date or Planned End Date.',
    'multiple_values'                                  => 'Multiple :attribute are not allowed.',
    'required_only_one_among'                          => 'Either :attribute or :values is required.',
    'recipient_country_region_percentage_sum'          => 'Sum of percentage of Recipient Country and Recipient Region must be equal to 100.',
    'unique_lang'                                      => 'Repeated :attribute in the same language is not allowed.',
    'not_in_spam_emails'                               => 'This email has been flagged as spam and cannot be used.',

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

    'attributes'                       => [
        'username'              => 'username',
        'full_name'             => 'full name',
        'email'                 => 'email',
        'publisher_id'          => 'publisher id',
        'publisher_name'        => 'publisher name',
        'identifier'            => 'identifier',
        'password'              => 'password',
        'password_confirmation' => 'password confirmation',
        'registration_agency'   => 'registration agency',
        'registration_number'   => 'registration number',
        'country'               => 'country',
        'default_language'      => 'default language',
        'publisher_type'        => 'publisher type',
        'license_id'            => 'license id',
        'description'           => 'description',
        'image_url'             => 'image url',
        'contact_email'         => 'contact email',
        'website'               => 'website',
        'source'                => 'source',
        'language_preference'   => 'language preference',
        'current_password'      => 'current password',
        'default_currency'      => 'default currency',
        'hierarchy'             => 'hierarchy',
        'budget_not_provided'   => 'budget not provided',
        'humanitarian'          => 'humanitarian',
        'api_token'             => 'api token',
        'narrative'             => 'narrative',
        'activity_identifier'   => 'activity identifier',
        'iati_identifier_text'  => 'iati identifier text',
    ],
    'date_greater_than'                => 'The date must be greater than :value',
    'logged_in_verify'                 => 'User must be logged in to verify email.',
    'attribute_exists'                 => 'The :attribute already exists.',
    'narrative_required_with_lang'     => 'The narrative is required when language is specified.',
    'narrative_required_with_xml_lang' => 'The narrative field is required with @xml:lang field.',
    'title_unique_lang'                => 'The title language field must be unique.',
    'first_title_required'             => 'The first title is required.',
    'xml_lang_unique'                  => 'The @xml:lang field must be unique.',
    'narrative_language_unique'        => 'The narrative language field must be unique.',
    'narrative_with_language'          => 'The narrative field is required with language field.',
    'xml_lang_invalid'                 => 'The @xml:lang field is invalid.',
    'narrative_is_required'            => 'The Narrative field is required.',
    'period_end_date'                  => 'Period end must be a date.',
    'period_end_gt_1900'               => 'Period end date must be date greater than year 1900.',
    'period_end_required'              => 'Period end is a required field',
    'period_end_after'                 => 'Period end must be a date after period',
    'document_link_format_invalid'     => 'The document link format is invalid',
    'url_valid'                        => 'The @url field must be a valid url.',
    'document_link_category_unique'    => 'The document link category code field must be a unique.',
    'document_link_category_invalid'   => 'The document link category code is invalid.',
    'document_link_language_unique'    => 'The document link language code field must be a unique.',
    'document_link_language_invalid'   => 'The document link language code is invalid.',
    'iso_proper_date'                  => 'The @iso-date field must be a proper date.',
    'iso_gt_1900'                      => 'The @iso-date field must be a greater than 1900.',
    'iso_date_required'                => 'The @iso-date field is required.',
    'iso_date_after'                   => 'The @iso-date field must be a date after period-start date.',
    'period_longer'                    => 'The period must not be longer than one year.',
    'description_type_invalid'         => 'The selected description type is invalid.',
    'vocabulary_uri_url'               => 'The @vocabulary-uri field must be a valid url.',
    'name_narrative_required'          => 'The narrative is required.',
    'registration_number_regex'        => 'The registration_number format is invalid.',
    'invalid_currency'                 => 'The value currency is invalid.',
    'invalid_ref'                      => 'The @ref format is invalid.',
    'reporting_org_ref_must_match'     => 'The @ref of reporting-org must match the organisation-identifier.',
    'language_required_with_narrative' => 'The language field is required when narrative field is present.',
    'amount_required'                  => 'The amount field is required.',
    'amount_numeric'                   => 'The amount must be numeric.',
    'amount_min'                       => 'The amount must not be in negative.',
    'amount_with_value'                => 'The amount field is required with value.',
    'amount_number'                    => 'The amount field must be a number.',
    'amount_negative'                  => 'The amount field must not be in negative.',
    'username_regex'                   => 'The username is invalid. Username must be purely lowercase alphabets followed by alphanumeric(ascii) characters and these symbols:-_',
    'email_unique'                     => 'Email is already in use in IATI Publisher.',
    'other_identifier'                 => [
        'regex'        => 'The other identifier reference field shouldn\'t contain the symbols /, &, | or ?.',
        'type_invalid' => 'The other identifier type is not valid.',
        'owner_org'    => [
            'reference_regex' => 'The owner org reference field shouldn\'t contain the symbols /, &, | or ?.',
        ]
    ],
    'activity_status'                  => [
        'in'   => 'The activity status does not exist.',
        'size' => 'The activity status cannot have more than one value.',
    ],
    'activity_date'                    => [
        'invalid'              => 'Date is invalid.',
        'date_before'          => 'Actual start and end dates may not be in the future.',
        'end_later_than_start' => 'End date must be later than the start date.',
        'invalid_type'         => 'The selected type is invalid.',
    ],
    'activity_scope'                   => [
        'in'   => 'The activity scope does not exist.',
        'size' => 'The activity scope cannot have more than one value.',
    ],
    'activity_recipient_country'       => [
        'already_in_transaction' => 'Recipient Country is already added at transaction level. You can add a Recipient Country either at activity level or at transaction level but not at both.',
        'invalid_code'           => 'The recipient country code is invalid.',
        'duplicate_country_code' => 'The Country Code cannot be redundant.',
        'percentage'             => [
            'numeric'                    => 'The recipient country percentage must be a number.',
            'max'                        => 'The recipient country percentage cannot be greater than 100',
            'sum_exceeded'               => 'The sum of recipient country percentage cannot be greater than 100',
            'min'                        => 'The recipient country percentage must be at least 0.',
            'region_percentage_complete' => 'Recipient Region’s percentage is already 100%. The sum of the percentages of Recipient Country and Recipient Region must be 100%.',
            'allocated_country_percent'  => 'The sum of percentages of Recipient Region(s) and Recipient country must be 100%',
        ],
    ],
    'activity_recipient_region'        => [
        'already_in_transaction' => 'Recipient Region is already added at transaction level. You can add a Recipient Region either at activity level or at transaction level but not at both.',
        'invalid_vocabulary'     => 'The recipient region vocabulary is invalid.',
        'invalid_code'           => 'The recipient region code is invalid.',
        'vocabulary_uri_url'     => 'The recipient region vocabulary uri must be a valid url.',
        'percentage'             => [
            'numeric'                                => 'The recipient region percentage field must be a number.',
            'country_percentage_complete'            => 'Recipient Country’s percentage is already 100%. The sum of the percentages of Recipient Country and Recipient Region must be 100%',
            'in'                                     => 'The sum of the percentages of Recipient Country(ies) and Recipient Region(s) must always be 100%',
            'allocated_region_total_mismatch'        => 'The sum of recipient country and recipient region of a specific vocabulary must be 100%',
            'sum_greater_than'                       => 'Sum of percentage within vocabulary cannot be greater than 100',
            'percentage_within_vocabulary'           => 'The sum of percentage of Recipient Country and Recipient Regions (within the same Region Vocabulary) must be equal to 100%',
            'min'                                    => 'The recipient country percentage must be at least 0.',
            'single_allocated_region_total_mismatch' => 'The sum of the percentages of Recipient Country(ies) and Recipient Region(s) must always be 100%',
        ],
    ],
    'activity_sector'                  => [
        'already_in_transactions' => 'Sector has already been declared at transaction level. You can’t declare a sector at the activity level.',
        'invalid_vocabulary'      => 'The sector vocabulary is invalid.',
        'invalid_code'            => 'The sector code is invalid.',
        'percentage'              => [
            'numeric'              => 'The percentage must be 100% or left empty, which is assumed as 100%.',
            'sector_total_percent' => 'The sum of percentages of same vocabulary must be equal to 100%',
        ],
    ],
    'activity_tag'                     => [
        'invalid_vocabulary'       => 'The tag vocabulary is invalid.',
        'invalid_sdg_code'         => 'The tag SDG code is invalid',
        'invalid_sdg_targets_code' => 'The tag SDG targets code is invalid.',
    ],
    'activity_policy_marker'           => [
        'invalid_significance' => 'The policy marker significance is invalid.',
        'invalid_code'         => 'The policy marker code is invalid.',
        'narrative_required'   => 'The narrative field is required when vocabulary is reporting organisation.'
    ],
    'activity_collaboration_type'      => [
        'in'   => 'The collaboration type does not exist.',
        'size' => 'The collaboration type cannot have more than one value.',
    ],
    'activity_default_flow_type'       => [
        'in'   => 'The default flow type does not exist.',
        'size' => 'The default flow type cannot have more than one value.',
    ],
    'activity_default_finance_type'    => [
        'in'   => 'The default finance type does not exist.',
        'size' => 'The default finance type cannot have more than one value.',
    ],
    'activity_default_aid_type'        => [
        'invalid_vocabulary'                  => 'The default aid type vocabulary is invalid.',
        'invalid'                             => 'The default aid type is invalid.',
        'invalid_earmarking_category'         => 'The default aid type earmarking category is invalid.',
        'invalid_earmarking_modality'         => 'The default aid type earmarking modality is invalid.',
        'invalid_cash_and_voucher_modalities' => 'The default aid type cash and voucher modalities is invalid.',
    ],
    'activity_default_tied_status'     => [
        'in'   => 'The default tied status does not exist.',
        'size' => 'The default tied status cannot have more than one value.',
    ],
    'activity_country_budget_items'    => [
        'invalid_vocabulary' => 'The country budget item vocabulary is invalid.',
        'invalid_code'       => 'The budget item code in invalid.',
        'percentage'         => [
            'numeric' => 'The budget item percentage field must be a number.',
            'max'     => 'The budget item percentage field cannot be greater than 100.',
            'sum'     => 'The sum of percentage with budget items must add up to 100.',
            'total'   => 'The budget item percentage field should be 100 when there is only one budget item.'
        ],
    ],
    'activity_humanitarian_scope'      => [
        'invalid_code'       => 'The humanitarian scope type is invalid.',
        'invalid_vocabulary' => 'The humanitarian scope vocabulary is invalid.',
        'code_string'        => 'The humanitarian scope code must be a string.',
        'vocabulary_uri_url' => 'The humanitarian scope vocabulary-uri must be a proper url.',
    ],
    'activity_capital_spend'           => [
        'numeric' => 'The capital spend must be a number',
        'between' => 'The capital spend must be a number between 0 and 100',
        'size'    => 'The capital spend cannot have more than one value.',
    ],
    'activity_related_activity'        => [
        'invalid_relationship_type' => 'The relationship type in related activity is invalid.',
    ],
    'activity_conditions'              => [
        'invalid_type' => 'The condition type is invalid.',
    ],
    'activity_contact_info'            => [
        'telephone' => [
            'numeric' => 'The contact info telephone number must be valid numeric value.',
            'regex'   => 'The contact info telephone number is invalid.',
            'min'     => 'The contact info telephone number must have atleast 7 digits.',
            'max'     => 'The contact info telephone number must not have more than 20 digits.',
        ],
        'email'     => [
            'valid'          => 'The contact info email must be valid.',
            'invalid_format' => 'The contact info email format is invalid.',
        ],
        'website'   => [
            'invalid_url' => 'The contact info website url must be valid url.',
        ],
    ],
    'activity_location'                => [
        'invalid_reach_code'          => 'The location reach code is invalid.',
        'invalid_location_exactness'  => 'The location exactness is invalid.',
        'invalid_location_class'      => 'The location class is invalid.',
        'invalid_feature_designation' => 'The location feature designation is invalid.',
        'invalid_vocabulary'          => 'The location id vocabulary is invalid.',
        'administrative'              => [
            'invalid_vocabulary' => 'The location administrative vocabulary is invalid.',
            'invalid_code'       => 'The location administrative code is invalid.',
            'level_min'          => 'The location administrative level must not have negative value.',
            'level_int'          => 'The location administrative level must be an integer.',
        ],
        'point'                       => [
            'latitude_numeric'  => 'The pos latitude must be numeric',
            'longitude_numeric' => 'The pos longitude must be numeric',
        ],
    ],
    'activity_planned_disbursement'    => [
        'invalid_type' => 'The planned disbursement type is invalid.',
        'provider_org' => [
            'invalid_type' => 'The planned disbursement provider org type is invalid.',
            'regex'        => 'The planned disbursement provider org ref shouldn\'t contain the symbols /, &, | or ?.',
        ],
        'receiver_org' => [
            'invalid_type' => 'The planned disbursement receiver org type is invalid.',
            'regex'        => 'The planned disbursement receiver org ref shouldn\'t contain the symbols /, &, | or ?.',
        ],
        'value'        => [
            'amount' => [
                'required' => 'Amount field is required',
                'numeric'  => 'Amount field must be a number',
                'min'      => 'Amount field must not be in negative.'
            ],
            'date'   => [
                'required'     => 'Value date is a required field',
                'invalid_date' => 'The Value Date must be a valid Date',
            ],
        ],
        'period_end'   => [
            'date'           => 'Period end must be a date',
            'gt_1900'        => 'Period end date must be date greater than year 1900.',
            'required'       => 'Period end is a required field',
            'after_or_equal' => 'Period end must be a date after period',
        ],
        'period_start' => [
            'date' => 'Period Start must be a date.',
        ],
        'date'         => [
            'period_start_end' => 'The Planned Disbursement Period must not be longer than three months',
        ],
    ],
    'activity_participating_org'       => [
        'invalid_identifier'       => 'The identifier must not contain symbols or blank space',
        'invalid_role'             => 'The participating organisation role is invalid.',
        'invalid_type'             => 'The participating organisation type is invalid.',
        'reference_required'       => 'The reference field is required when name is empty.',
        'invalid_crs_channel_code' => 'The Crs Channel Code is invalid.',
        'name_required'            => 'The name field is required when participating org reference is empty.',
    ],
    'activity_budget'                  => [
        'budget'     => [
            'budgets_identical' => 'The periods of multiple budgets with the same type should not be the same',
            'invalid_type'      => 'The budget type is invalid.',
            'invalid_status'    => 'The budget status is invalid.',
        ],
        'period_end' => [
            'before' => 'The Period End iso-date must be within a year after Period Start iso-date.',
        ],
        'date'       => [
            'date'             => 'The iso-date field must be a valid date.',
            'period_start_end' => 'The Budget Period must not be longer than one year',
            'gt_1900'          => 'The iso-date field must be date after year 1900.',
            'after'            => 'The Period End iso-date must be a date after Period Start iso-date',
        ],
        'value'      => [
            'date' => 'The value-date field must be a valid date.',
        ],
    ],
    'activity_transactions'            => [
        'transaction_id'             => [
            'same_activity' => 'All transactions must belong to the same activity.',
            'mismatch'      => 'Transaction IDs do not match the specified activity.',
            'unpublish'     => 'Please unpublish activity before deleting transactions.',
        ],
        'invalid_type'               => 'The transaction type is invalid.',
        'invalid_flow_type'          => 'The transaction flow type code is invalid.',
        'invalid_finance_type'       => 'The transaction finance type code is invalid.',
        'country_or_region'          => 'You must add either recipient country or recipient region.',
        'country_region_in_activity' => 'Recipient Region or Recipient Country is already added at activity level. You can add a Recipient Region and or Recipient Country either at activity level or at transaction level.',
        'sector_in_activity'         => 'Sector has already been declared at activity level. You can’t declare a sector at the transaction level. To declare at transaction level, you need to remove sector at activity level.',
        'aid_type'                   => [
            'invalid_vocabulary'  => 'The transaction aid type vocabulary is invalid.',
            'invalid_code'        => 'The transaction aid type code is invalid.',
            'invalid_status_code' => 'The transaction tied status code is invalid.',
        ],
        'date'                       => [
            'before' => 'The @iso-date must not be in future.',
            'date'   => 'The @iso-date field must be a valid date.',
        ],
        'value'                      => [
            'numeric_amount' => 'The @amount field must be a number.',
            'date'           => [
                'before' => 'The @value-date must not be in future.',
                'date'   => 'The @value-date field must be a valid date.',
            ],
        ],
        'sector'                     => [
            'required'           => 'You have declared sector at transaction level so you must declare sector for all the transactions.',
            'invalid_code'       => 'The transaction sector code is invalid.',
            'vocabulary_uri_url' => 'The transaction sector vocabulary-uri field must be a valid url.',
        ],
        'provider_org'               => [
            'invalid' => 'The transaction provider org type is invalid.',
        ],
        'receiver_org'               => [
            'exclude_operators' => 'The transaction receiver-activity-id field is not valid.',
            'invalid_type'      => 'The transaction receiver org type is invalid.',
        ],
        'recipient_region'           => [
            'invalid_vocabulary'        => 'The transaction recipient region vocabulary is invalid.',
            'invalid_region_code'       => 'The transaction recipient region code is invalid.',
            'vocabulary_uri_url'        => 'The transaction recipient region vocabulary uri must be a valid url.',
            'region_vocabulary_uri_url' => 'The @vocabulary-uri field must be a valid url.',
        ],
        'recipient_country'          => [
            'invalid_code' => 'The transaction recipient country code is invalid.',
        ],
    ],
    'activity_results'                 => [
        'result_id' => [
            'same_activity' => 'All results must belong to the same activity.',
            'no_match'      => 'Results IDs do not match the specified activity.',
            'unpublish'     => 'Please unpublish activity before deleting results.',
        ],
        'reference' => [
            'vocabulary_uri_url' => 'The @vocabulary-uri field must be a valid url.',
            'code_present'       => 'The code is already defined in its indicators',
            'vocabulary_present' => 'The vocabulary is already defined in its indicators',
        ],
    ],
    'activity_indicators'              => [
        'invalid_measure'            => 'The indicator measure is invalid.',
        'invalid_aggregation_status' => 'The indicator aggregation status is invalid.',
        'invalid_ascending'          => 'The indicator ascending is invalid.',
        'reference'                  => [
            'uri_url'                       => 'The @indicator-uri field must be a valid url.',
            'result_ref_code_present'       => 'The code is already defined in its result',
            'result_ref_vocabulary_present' => 'The vocabulary is already defined in its result',
        ],
        'baseline'                   => [
            'year'  => [
                'invalid_year' => 'The @year field is not valid.',
                'in'           => 'The @year field should be the year of baseline date',
                'digits'       => 'The @year field must have 4 digits.',
            ],
            'value' => [
                'numeric' => 'The @value field must be a number.',
                'gte'     => 'The @value field must be greater or equal to 0.',
            ],
        ],
    ],
    'activity_periods'                 => [
        'date'  => [
            'date'             => 'The @date field must be a proper date.',
            'after'            => 'The @iso-date field of period end must be a date after @iso-field of period start',
            'gte_1900'         => 'The @iso-date must be greater than 1900',
            'period_start_end' => 'The @iso-date field of period end and @iso-date of period start must not have difference of more than a year',
        ],
        'value' => [
            'numeric'           => 'The @value field must be numeric.',
            'qualitative_empty' => 'Value must be omitted when the indicator measure is qualitative.',
            'target_required'   => 'Target value is required if actual value is not provided.',
            'actual_required'   => 'Actual value is required if target value is not provided.',
        ],
    ],
    'activity_upload'                  => [
        'required'          => 'The activity file must be uploaded',
        'activity_file'     => 'The file must be of either xml or csv format.',
        'max'               => 'The file shouldn\'t be greater than 10MB.',
        'xls_required'      => 'The xls file must be uploaded',
        'xls_activity_file' => 'The file must be of xls format.',
    ],
    'organization_document_link'       => [
        'category_code' => [
            'unique'   => 'The category @code field must be unique.',
            'required' => 'The @code field is required.',
        ],
        'language'      => [
            'unique' => 'The language @code field must be unique.',
        ],
    ],
    'value_date_required'              => 'The @value-date field is required.',
    'value_date_date'                  => 'The @value-date must be a date.',
    'value_date_after_or_equal'        => 'The value date field must be a date between period start and period end',
    'value_date_with_value'            => 'The @value-date is required with value.',
    'value_date_after_equal'           => 'The @value-date field must be a date between period start and period end',
    'activity_not_exist'               => 'Activity does not exist',
    'result_not_exist'                 => 'Result does not exist',
    'transaction_not_exist'            => 'Transaction does not exist',
    'indicator_not_exist'              => 'Indicator does not exist',
    'period_not_exist'                 => 'Period does not exist',
    'these_credentials_do_not_match_our_records'=>'These credentials do not match our records.',
    'your_account_is_inactive'=>'Your account is inactive. Please contact your admin or superadmin for further information.'
];
