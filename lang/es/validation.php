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

    'accepted'             => 'es_ The :attribute must be accepted.',
    'accepted_if'          => 'es_ The :attribute must be accepted when :other is :value.',
    'active_url'           => 'es_ The :attribute is not a valid URL.',
    'after'                => 'es_ The :attribute must be a date after :date.',
    'after_or_equal'       => 'es_ The :attribute must be a date after or equal to :date.',
    'alpha'                => 'es_ The :attribute must only contain letters.',
    'alpha_dash'           => 'es_ The :attribute must only contain letters, numbers, dashes and underscores.',
    'alpha_num'            => 'es_ The :attribute must only contain letters and numbers.',
    'array'                => 'es_ The :attribute must be an array.',
    'before'               => 'es_ The :attribute must be a date before :date.',
    'before_or_equal'      => 'es_ The :attribute must be a date before or equal to :date.',
    'between'              => [
        'numeric' => 'es_ The :attribute must be between :min and :max.',
        'file'    => 'es_ The :attribute must be between :min and :max kilobytes.',
        'string'  => 'es_ The :attribute must be between :min and :max characters.',
        'array'   => 'es_ The :attribute must have between :min and :max items.',
    ],
    'boolean'              => 'es_ The :attribute field must be true or false.',
    'confirmed'            => 'es_ The :attribute confirmation does not match.',
    'current_password'     => 'es_ The password is incorrect.',
    'date'                 => 'es_ The :attribute is not a valid date.',
    'date_equals'          => 'es_ The :attribute must be a date equal to :date.',
    'date_format'          => 'es_ The :attribute does not match the format :format.',
    'declined'             => 'es_ The :attribute must be declined.',
    'declined_if'          => 'es_ The :attribute must be declined when :other is :value.',
    'different'            => 'es_ The :attribute and :other must be different.',
    'digits'               => 'es_ The :attribute must be :digits digits.',
    'digits_between'       => 'es_ The :attribute must be between :min and :max digits.',
    'dimensions'           => 'es_ The :attribute has invalid image dimensions.',
    'distinct'             => 'es_ The :attribute field has a duplicate value.',
    'email'                => 'es_ The :attribute must be a valid email address.',
    'ends_with'            => 'es_ The :attribute must end with one of the following: :values.',
    'enum'                 => 'es_ The selected :attribute is invalid.',
    'exists'               => 'es_ The selected :attribute is invalid.',
    'file'                 => 'es_ The :attribute must be a file.',
    'filled'               => 'es_ The :attribute field must have a value.',
    'gt'                   => [
        'numeric' => 'es_ The :attribute must be greater than :value.',
        'file'    => 'es_ The :attribute must be greater than :value kilobytes.',
        'string'  => 'es_ The :attribute must be greater than :value characters.',
        'array'   => 'es_ The :attribute must have more than :value items.',
    ],
    'gte'                  => [
        'numeric' => 'es_ The :attribute must be greater than or equal to :value.',
        'file'    => 'es_ The :attribute must be greater than or equal to :value kilobytes.',
        'string'  => 'es_ The :attribute must be greater than or equal to :value characters.',
        'array'   => 'es_ The :attribute must have :value items or more.',
    ],
    'image'                => 'es_ The :attribute must be an image.',
    'in'                   => 'es_ The selected :attribute is invalid.',
    'in_array'             => 'es_ The :attribute field does not exist in :other.',
    'integer'              => 'es_ The :attribute must be an integer.',
    'ip'                   => 'es_ The :attribute must be a valid IP address.',
    'ipv4'                 => 'es_ The :attribute must be a valid IPv4 address.',
    'ipv6'                 => 'es_ The :attribute must be a valid IPv6 address.',
    'json'                 => 'es_ The :attribute must be a valid JSON string.',
    'lt'                   => [
        'numeric' => 'es_ The :attribute must be less than :value.',
        'file'    => 'es_ The :attribute must be less than :value kilobytes.',
        'string'  => 'es_ The :attribute must be less than :value characters.',
        'array'   => 'es_ The :attribute must have less than :value items.',
    ],
    'lte'                  => [
        'numeric' => 'es_ The :attribute must be less than or equal to :value.',
        'file'    => 'es_ The :attribute must be less than or equal to :value kilobytes.',
        'string'  => 'es_ The :attribute must be less than or equal to :value characters.',
        'array'   => 'es_ The :attribute must not have more than :value items.',
    ],
    'mac_address'          => 'es_ The :attribute must be a valid MAC address.',
    'max'                  => [
        'numeric' => 'es_ The :attribute must not be greater than :max.',
        'file'    => 'es_ The :attribute must not be greater than :max kilobytes.',
        'string'  => 'es_ The :attribute must not be greater than :max characters.',
        'array'   => 'es_ The :attribute must not have more than :max items.',
    ],
    'mimes'                => 'es_ The :attribute must be a file of type: :values.',
    'mimetypes'            => 'es_ The :attribute must be a file of type: :values.',
    'min'                  => [
        'numeric' => 'es_ The :attribute must be at least :min.',
        'file'    => 'es_ The :attribute must be at least :min kilobytes.',
        'string'  => 'es_ The :attribute must be at least :min characters.',
        'array'   => 'es_ The :attribute must have at least :min items.',
    ],
    'multiple_of'          => 'es_ The :attribute must be a multiple of :value.',
    'not_in'               => 'es_ The selected :attribute is invalid.',
    'not_regex'            => 'es_ The :attribute format is invalid.',
    'numeric'              => 'es_ The :attribute must be a number.',
    'password'             => 'es_ The password is incorrect.',
    'present'              => 'es_ The :attribute field must be present.',
    'prohibited'           => 'es_ The :attribute field is prohibited.',
    'prohibited_if'        => 'es_ The :attribute field is prohibited when :other is :value.',
    'prohibited_unless'    => 'es_ The :attribute field is prohibited unless :other is in :values.',
    'prohibits'            => 'es_ The :attribute field prohibits :other from being present.',
    'regex'                => 'es_ The :attribute format is invalid.',
    'required'             => 'es_ The :attribute field is required.',
    'required_array_keys'  => 'es_ The :attribute field must contain entries for: :values.',
    'required_if'          => 'es_ The :attribute field is required when :other is :value.',
    'required_unless'      => 'es_ The :attribute field is required unless :other is in :values.',
    'required_with'        => 'es_ The :attribute field is required when :values is present.',
    'required_with_all'    => 'es_ The :attribute field is required when :values are present.',
    'required_without'     => 'es_ The :attribute field is required when :values is not present.',
    'required_without_all' => 'es_ The :attribute field is required when none of :values are present.',
    'same'                 => 'es_ The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'es_ The :attribute must be :size.',
        'file'    => 'es_ The :attribute must be :size kilobytes.',
        'string'  => 'es_ The :attribute must be :size characters.',
        'array'   => 'es_ The :attribute must contain :size items.',
    ],
    'starts_with'          => 'es_ The :attribute must start with one of the following: :values.',
    'string'               => 'es_ The :attribute must be a string.',
    'timezone'             => 'es_ The :attribute must be a valid timezone.',
    'unique'               => 'es_ The :attribute has already been taken.',
    'uploaded'             => 'es_ The :attribute failed to upload.',
    'url'                  => 'es_ The :attribute must be a valid URL.',
    'uuid'                 => 'es_ The :attribute must be a valid UUID.',

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

    'custom'                                           => [
        'attribute-name' => [
            'rule-name' => 'es_ custom-message',
        ],
    ],

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
    'sum'                                              => 'es_ The sum of percentage within a vocabulary must add up to 100.',
    'overall_sum'                                      => 'es_ The sum of percentage must not be more than 100.',
    'required_custom'                                  => 'es_ :attribute is required when there are multiple codes.',
    'total'                                            => 'es_ :attribute should be 100 when there is only one :values.',
    'csv_required'                                     => 'es_ At row :number :attribute is required',
    'csv_unique'                                       => 'es_ At row :number :attribute should be unique',
    'csv_invalid'                                      => 'es_ At row :number :attribute is invalid',
    'csv_numeric'                                      => 'es_ At row :number :attribute should be numeric',
    'csv_unique_validation'                            => 'es_ At row :number :attribute is invalid and must be unique.',
    'csv_among'                                        => 'es_ At row :number at least one :type among :attribute is required.',
    'csv_only_one'                                     => 'es_ At row :number only one among :attribute is required.',
    'year_value_narrative_validation'                  => 'es_ :year and :value is required if :narrative is not empty.',
    'year_narrative_validation'                        => 'es_ :year is required if :narrative is not empty.',
    'org_required'                                     => 'es_ At least one organisation name is required',
    'custom_unique'                                    => 'es_ :attribute has already been taken.',
    'user_identifier_taken'                            => 'es_ Sorry! this User Identifier is already taken',
    'enter_valid'                                      => 'es_ Please enter valid :attribute',
    'sector_validation'                                => 'es_ Sector must be present either at Activity or in all Transactions level.',
    'sector_narrative'                                 => 'es_ <a href=\'%s\' >Sector Narrative</a> is required when vocabulay is 98 or 99.',
    'transaction_sector_narrative'                     => 'es_ <a href=\'%s\' >Transaction Sector Narrative</a> is required when vocabulay is 98 or 99.',
    'transaction_sector_validation'                    => 'es_ All Transactions must contain Sector element.',
    'sector_in_activity_and_transaction_remove'        => 'es_ You can only mention Sector either at Activity or in Transaction level(should be included in all transactions) but not both. <br/>Please click the link to remove Sector From: <a href=\'%s\' class=\'delete_data\'>Transaction Level</a> OR <a href=\'%s\' class=\'delete_data\'>Activity Level</a>',
    'sector_in_activity_and_transaction'               => 'es_ You need to mention either Recipient Country or Region either in Activity Level or in Transaction level. You can\'t have Country/Region in both Activity level and Transaction level. Also, they cannot be empty for both activity and transactions',
    'recipient_country_or_region_required'             => 'es_ Either Recipient Country or Recipient Region is required in Activity Level or Transaction Level.',
    'transaction_recipient_country_or_region_required' => 'es_ All Transactions must contain Recipient Region or Recipient Country',
    'sum_of_percentage'                                => 'es_ The sum of percentage in :attribute must be 100.',
    'validation_before_completed'                      => 'es_ Please make sure you enter the following fields before changing to completed state.',
    'reporting_org_identifier_unique'                  => 'es_ This reporting organization identifier is being used by :orgName. This identifier has to be unique. Please contact us at support@aidstream.org',
    'code_list'                                        => 'es_ :attribute is not valid.',
    'string'                                           => 'es_ :attribute should be string',
    'negative'                                         => 'es_ :attribute cannot be negative',
    'actual_date'                                      => 'es_ Actual Start Date And Actual End Date must not exceed present date',
    'multiple_activity_date'                           => 'es_ Multiple Activity dates are not allowed.',
    'start_end_date'                                   => 'es_ Actual Start Date or Planned Start Date should be before Actual End Date or Planned End Date.',
    'csv_date'                                         => 'es_ :attribute must be of format Y-m-d.',
    'multiple_values'                                  => 'es_ Multiple :attribute are not allowed.',
    'csv_size'                                         => 'es_ At least one :attribute is required',
    'multiple_narratives'                              => 'es_ Multiple narratives for :attribute with the same type is not allowed.',
    'required_only_one_among'                          => 'es_ Either :attribute or :values is required.',
    'recipient_country_region_percentage_sum'          => 'es_ Sum of percentage of Recipient Country and Recipient Region must be equal to 100.',
    'invalid_in_transaction'                           => 'es_ Entered :attribute is incorrect in Transaction.',
    'invalid_in_sector'                                => 'es_ Entered :attribute is incorrect in Sector.',
    'required_if_in_transaction'                       => 'es_ :attribute is required if :values is not present in Transaction.',
    'sector_vocabulary_required'                       => 'es_ Sector Vocabulary is required in Transaction if not present in Activity Level.',
    'required_in_transaction'                          => 'es_ :attribute is required in Transaction.',
    'invalid_language'                                 => 'es_ Invalid :attribute language',
    'unique_lang'                                      => 'es_ Repeated :attribute in the same language is not allowed.',
    'indicator_ascending'                              => 'es_ Indicator Ascending should be true/false, 0/1 or Yes/No.',
    'indicator_size'                                   => 'es_ Indicator Baseline Year or Value should occur once and no more than once within an Indicator.',
    'narrative_required'                               => 'es_ :attribute Narrative is required.',
    'no_more_than_once'                                => 'es_ :attribute should occur once and no more than once within :values.',
    'budget_period_end_date'                           => 'es_ Budget Period End Date',
    'spaces_not_allowed'                               => 'es_ You cannot enter spaces in organization name abbreviation.',
    'custom'                                           => [
        'attribute-name' => [
            'rule-name' => 'es_ custom-message',
        ],
    ],
    'not_in_spam_emails'                               => 'es_ This email has been flagged as spam and cannot be used.',
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
        'username'              => 'es_ username',
        'full_name'             => 'es_ full name',
        'email'                 => 'es_ email',
        'publisher_id'          => 'es_ publisher id',
        'publisher_name'        => 'es_ publisher name',
        'identifier'            => 'es_ identifier',
        'password'              => 'es_ password',
        'password_confirmation' => 'es_ password confirmation',
        'registration_agency'   => 'es_ registration agency',
        'registration_number'   => 'es_ registration number',
        'country'               => 'es_ country',
        'default_language'      => 'es_ default language',
        'publisher_type'        => 'es_ publisher type',
        'license_id'            => 'es_ license id',
        'description'           => 'es_ description',
        'image_url'             => 'es_ image url',
        'contact_email'         => 'es_ contact email',
        'website'               => 'es_ website',
        'source'                => 'es_ source',
        'language_preference'   => 'es_ language preference',
        'current_password'      => 'es_ current password',
        'default_currency'      => 'es_ default currency',
        'hierarchy'             => 'es_ hierarchy',
        'budget_not_provided'   => 'es_ budget not provided',
        'humanitarian'          => 'es_ humanitarian',
        'api_token'             => 'es_ api token',
        'narrative'             => 'es_ narrative',
        'activity_identifier'   => 'es_ activity identifier',
        'iati_identifier_text'  => 'es_ iati identifier text',
    ],
    'within_a_year'                    => 'es_ The :attribute must be within a year after :date.',
    'required_if_any'                  => 'es_ The :field is required if any of the fields of :element are filled.',
    'amount'                           => 'es_ Please enter amount in the format xx.xx',
    'greater'                          => 'es_ The :attribute must be greater than or equal to :value',
    'date_greater_than'                => 'es_ The date must be greater than :value',
    'logged_in_verify'                 => 'es_ User must be logged in to verify email.',
    'attribute_exists'                 => 'es_ The :attribute already exists.',
    'narrative_required_with_lang'     => 'es_ The narrative is required when language is specified.',
    'narrative_required_with_xml_lang' => 'es_ The narrative field is required with @xml:lang field.',
    'title_unique_lang'                => 'es_ The title language field must be unique.',
    'first_title_required'             => 'es_ The first title is required.',
    'xml_lang_unique'                  => 'es_ The @xml:lang field must be unique.',
    'narrative_language_unique'        => 'es_ The narrative language field must be unique.',
    'narrative_with_language'          => 'es_ The narrative field is required with language field.',
    'xml_lang_invalid'                 => 'es_ The @xml:lang field is invalid.',
    'narrative_is_required'            => 'es_ The Narrative field is required.',
    'period_end_date'                  => 'es_ Period end must be a date.',
    'period_end_gt_1900'               => 'es_ Period end date must be date greater than year 1900.',
    'period_end_required'              => 'es_ Period end is a required field',
    'period_end_after'                 => 'es_ Period end must be a date after period',
    'document_link_format_invalid'     => 'es_ The document link format is invalid',
    'url_valid'                        => 'es_ The @url field must be a valid url.',
    'document_link_category_unique'    => 'es_ The document link category code field must be a unique.',
    'document_link_category_invalid'   => 'es_ The document link category code is invalid.',
    'document_link_language_unique'    => 'es_ The document link language code field must be a unique.',
    'document_link_language_invalid'   => 'es_ The document link language code is invalid.',
    'iso_proper_date'                  => 'es_ The @iso-date field must be a proper date.',
    'iso_gt_1900'                      => 'es_ The @iso-date field must be a greater than 1900.',
    'iso_date_required'                => 'es_ The @iso-date field is required.',
    'iso_date_after'                   => 'es_ The @iso-date field must be a date after period-start date.',
    'period_longer'                    => 'es_ The period must not be longer than one year.',
    'description_type_invalid'         => 'es_ The selected description type is invalid.',
    'vocabulary_uri_url'               => 'es_ The @vocabulary-uri field must be a valid url.',
    'name_narrative_required'          => 'es_ The narrative is required.',
    'registration_number_regex'        => 'es_ The registration_number format is invalid.',
    'invalid_currency'                 => 'es_ The value currency is invalid.',
    'invalid_ref'                      => 'es_ The @ref format is invalid.',
    'reporting_org_ref_must_match'     => 'es_ The @ref of reporting-org must match the organisation-identifier.',
    'language_required_with_narrative' => 'es_ The language field is required when narrative field is present.',
    'amount_required'                  => 'es_ The amount field is required.',
    'amount_numeric'                   => 'es_ The amount must be numeric.',
    'amount_min'                       => 'es_ The amount must not be in negative.',
    'amount_with_value'                => 'es_ The amount field is required with value.',
    'amount_number'                    => 'es_ The amount field must be a number.',
    'amount_negative'                  => 'es_ The amount field must not be in negative.',
    'username_regex'                   => 'es_ The username is invalid. Username must be purely lowercase alphabets followed by alphanumeric(ascii) characters and these symbols:-_',
    'email_unique'                     => 'es_ Email is already in use in IATI Publisher.',
    'other_identifier'                 => [
        'regex'        => 'es_ The other identifier reference field shouldn\'t contain the symbols /, &, | or ?.',
        'type_invalid' => 'es_ The other identifier type is not valid.',
        'owner_org'    => [
            'reference_regex' => 'es_ The owner org reference field shouldn\'t contain the symbols /, &, | or ?.',
        ]
    ],
    'activity_status'                  => [
        'in'   => 'es_ The activity status does not exist.',
        'size' => 'es_ The activity status cannot have more than one value.',
    ],
    'activity_date'                    => [
        'invalid'              => 'es_ Date is invalid.',
        'date_before'          => 'es_ Actual start and end dates may not be in the future.',
        'end_later_than_start' => 'es_ End date must be later than the start date.',
        'invalid_type'         => 'es_ The selected type is invalid.',
    ],
    'activity_scope'                   => [
        'in'   => 'es_ The activity scope does not exist.',
        'size' => 'es_ The activity scope cannot have more than one value.',
    ],
    'activity_recipient_country'       => [
        'already_in_transaction' => 'es_ Recipient Country is already added at transaction level. You can add a Recipient Country either at activity level or at transaction level but not at both.',
        'invalid_code'           => 'es_ The recipient country code is invalid.',
        'duplicate_country_code' => 'es_ The Country Code cannot be redundant.',
        'percentage'             => [
            'numeric'                    => 'es_ The recipient country percentage must be a number.',
            'max'                        => 'es_ The recipient country percentage cannot be greater than 100',
            'sum_exceeded'               => 'es_ The sum of recipient country percentage cannot be greater than 100',
            'min'                        => 'es_ The recipient country percentage must be at least 0.',
            'region_percentage_complete' => 'es_ Recipient Region’s percentage is already 100%. The sum of the percentages of Recipient Country and Recipient Region must be 100%.',
            'allocated_country_percent'  => 'es_ The sum of percentages of Recipient Region(s) and Recipient country must be 100%',
        ],
    ],
    'activity_recipient_region'        => [
        'already_in_transaction' => 'es_ Recipient Region is already added at transaction level. You can add a Recipient Region either at activity level or at transaction level but not at both.',
        'invalid_vocabulary'     => 'es_ The recipient region vocabulary is invalid.',
        'invalid_code'           => 'es_ The recipient region code is invalid.',
        'vocabulary_uri_url'     => 'es_ The recipient region vocabulary uri must be a valid url.',
        'percentage'             => [
            'numeric'                                => 'es_ The recipient region percentage field must be a number.',
            'country_percentage_complete'            => 'es_ Recipient Country’s percentage is already 100%. The sum of the percentages of Recipient Country and Recipient Region must be 100%',
            'in'                                     => 'es_ The sum of the percentages of Recipient Country(ies) and Recipient Region(s) must always be 100%',
            'allocated_region_total_mismatch'        => 'es_ The sum of recipient country and recipient region of a specific vocabulary must be 100%',
            'sum_greater_than'                       => 'es_ Sum of percentage within vocabulary cannot be greater than 100',
            'percentage_within_vocabulary'           => 'es_ The sum of percentage of Recipient Country and Recipient Regions (within the same Region Vocabulary) must be equal to 100%',
            'min'                                    => 'es_ The recipient country percentage must be at least 0.',
            'single_allocated_region_total_mismatch' => 'es_ The sum of the percentages of Recipient Country(ies) and Recipient Region(s) must always be 100%',
        ],
    ],
    'activity_sector'                  => [
        'already_in_transactions' => 'es_ Sector has already been declared at transaction level. You can’t declare a sector at the activity level.',
        'invalid_vocabulary'      => 'es_ The sector vocabulary is invalid.',
        'invalid_code'            => 'es_ The sector code is invalid.',
        'percentage'              => [
            'numeric'              => 'es_ The percentage must be 100% or left empty, which is assumed as 100%.',
            'sector_total_percent' => 'es_ The sum of percentages of same vocabulary must be equal to 100%',
        ],
    ],
    'activity_tag'                     => [
        'invalid_vocabulary'       => 'es_ The tag vocabulary is invalid.',
        'invalid_sdg_code'         => 'es_ The tag SDG code is invalid',
        'invalid_sdg_targets_code' => 'es_ The tag SDG targets code is invalid.',
    ],
    'activity_policy_marker'           => [
        'invalid_significance' => 'es_ The policy marker significance is invalid.',
        'invalid_code'         => 'es_ The policy marker code is invalid.',
        'narrative_required'   => 'es_ The narrative field is required when vocabulary is reporting organisation.'
    ],
    'activity_collaboration_type'      => [
        'in'   => 'es_ The collaboration type does not exist.',
        'size' => 'es_ The collaboration type cannot have more than one value.',
    ],
    'activity_default_flow_type'       => [
        'in'   => 'es_ The default flow type does not exist.',
        'size' => 'es_ The default flow type cannot have more than one value.',
    ],
    'activity_default_finance_type'    => [
        'in'   => 'es_ The default finance type does not exist.',
        'size' => 'es_ The default finance type cannot have more than one value.',
    ],
    'activity_default_aid_type'        => [
        'invalid_vocabulary'                  => 'es_ The default aid type vocabulary is invalid.',
        'invalid'                             => 'es_ The default aid type is invalid.',
        'invalid_earmarking_category'         => 'es_ The default aid type earmarking category is invalid.',
        'invalid_earmarking_modality'         => 'es_ The default aid type earmarking modality is invalid.',
        'invalid_cash_and_voucher_modalities' => 'es_ The default aid type cash and voucher modalities is invalid.',
    ],
    'activity_default_tied_status'     => [
        'in'   => 'es_ The default tied status does not exist.',
        'size' => 'es_ The default tied status cannot have more than one value.',
    ],
    'activity_country_budget_items'    => [
        'invalid_vocabulary' => 'es_ The country budget item vocabulary is invalid.',
        'invalid_code'       => 'es_ The budget item code in invalid.',
        'percentage'         => [
            'numeric' => 'es_ The budget item percentage field must be a number.',
            'max'     => 'es_ The budget item percentage field cannot be greater than 100.',
            'sum'     => 'es_ The sum of percentage with budget items must add up to 100.',
            'total'   => 'es_ The budget item percentage field should be 100 when there is only one budget item.'
        ],
    ],
    'activity_humanitarian_scope'      => [
        'invalid_code'       => 'es_ The humanitarian scope type is invalid.',
        'invalid_vocabulary' => 'es_ The humanitarian scope vocabulary is invalid.',
        'code_string'        => 'es_ The humanitarian scope code must be a string.',
        'vocabulary_uri_url' => 'es_ The humanitarian scope vocabulary-uri must be a proper url.',
    ],
    'activity_capital_spend'           => [
        'numeric' => 'es_ The capital spend must be a number',
        'between' => 'es_ The capital spend must be a number between 0 and 100',
        'size'    => 'es_ The capital spend cannot have more than one value.',
    ],
    'activity_related_activity'        => [
        'invalid_relationship_type' => 'es_ The relationship type in related activity is invalid.',
    ],
    'activity_conditions'              => [
        'invalid_type' => 'es_ The condition type is invalid.',
    ],
    'activity_contact_info'            => [
        'telephone' => [
            'numeric' => 'es_ The contact info telephone number must be valid numeric value.',
            'regex'   => 'es_ The contact info telephone number is invalid.',
            'min'     => 'es_ The contact info telephone number must have atleast 7 digits.',
            'max'     => 'es_ The contact info telephone number must not have more than 20 digits.',
        ],
        'email'     => [
            'valid'          => 'es_ The contact info email must be valid.',
            'invalid_format' => 'es_ The contact info email format is invalid.',
        ],
        'website'   => [
            'invalid_url' => 'es_ The contact info website url must be valid url.',
        ],
    ],
    'activity_location'                => [
        'invalid_reach_code'          => 'es_ The location reach code is invalid.',
        'invalid_location_exactness'  => 'es_ The location exactness is invalid.',
        'invalid_location_class'      => 'es_ The location class is invalid.',
        'invalid_feature_designation' => 'es_ The location feature designation is invalid.',
        'invalid_vocabulary'          => 'es_ The location id vocabulary is invalid.',
        'administrative'              => [
            'invalid_vocabulary' => 'es_ The location administrative vocabulary is invalid.',
            'invalid_code'       => 'es_ The location administrative code is invalid.',
            'level_min'          => 'es_ The location administrative level must not have negative value.',
            'level_int'          => 'es_ The location administrative level must be an integer.',
        ],
        'point'                       => [
            'latitude_numeric'  => 'es_ The pos latitude must be numeric',
            'longitude_numeric' => 'es_ The pos longitude must be numeric',
        ],
    ],
    'activity_planned_disbursement'    => [
        'invalid_type' => 'es_ The planned disbursement type is invalid.',
        'provider_org' => [
            'invalid_type' => 'es_ The planned disbursement provider org type is invalid.',
            'regex'        => 'es_ The planned disbursement provider org ref shouldn\'t contain the symbols /, &, | or ?.',
        ],
        'receiver_org' => [
            'invalid_type' => 'es_ The planned disbursement receiver org type is invalid.',
            'regex'        => 'es_ The planned disbursement receiver org ref shouldn\'t contain the symbols /, &, | or ?.',
        ],
        'value'        => [
            'amount' => [
                'required' => 'es_ Amount field is required',
                'numeric'  => 'es_ Amount field must be a number',
                'min'      => 'es_ Amount field must not be in negative.'
            ],
            'date'   => [
                'required'     => 'es_ Value date is a required field',
                'invalid_date' => 'es_ The Value Date must be a valid Date',
            ],
        ],
        'period_end'   => [
            'date'           => 'es_ Period end must be a date field.',
            'gt_1900'        => 'es_ Period end date must be date greater than year 1900.',
            'required'       => 'es_ Period end is a required field',
            'after_or_equal' => 'es_ Period end must be a date after period',
        ],
        'period_start' => [
            'date' => 'es_ Period Start must be a date field.',
        ],
        'date'         => [
            'period_start_end' => 'es_ The Planned Disbursement Period must not be longer than three months',
        ],
    ],
    'activity_participating_org'       => [
        'invalid_identifier'       => 'es_ The identifier must not contain symbols or blank space',
        'invalid_role'             => 'es_ The participating organisation role is invalid.',
        'invalid_type'             => 'es_ The participating organisation type is invalid.',
        'reference_required'       => 'es_ The reference field is required when name is empty.',
        'invalid_crs_channel_code' => 'es_ The Crs Channel Code is invalid.',
        'name_required'            => 'es_ The name field is required when participating org reference is empty.',
    ],
    'activity_budget'                  => [
        'budget'     => [
            'budgets_identical' => 'es_ The periods of multiple budgets with the same type should not be the same',
            'invalid_type'      => 'es_ The budget type is invalid.',
            'invalid_status'    => 'es_ The budget status is invalid.',
        ],
        'period_end' => [
            'before' => 'es_ The Period End iso-date must be within a year after Period Start iso-date.',
        ],
        'date'       => [
            'date'             => 'es_ The iso-date field must be a valid date.',
            'period_start_end' => 'es_ The Budget Period must not be longer than one year',
            'gt_1900'          => 'es_ The iso-date field must be date after year 1900.',
            'after'            => 'es_ The Period End iso-date must be a date after Period Start iso-date',
        ],
        'value'      => [
            'date' => 'es_ The value-date field must be a valid date.',
        ],
    ],
    'activity_transactions'            => [
        'transaction_id'             => [
            'same_activity' => 'es_ All transactions must belong to the same activity.',
            'mismatch'      => 'es_ Transaction IDs do not match the specified activity.',
            'unpublish'     => 'es_ Please unpublish activity before deleting transactions.',
        ],
        'invalid_type'               => 'es_ The transaction type is invalid.',
        'invalid_flow_type'          => 'es_ The transaction flow type code is invalid.',
        'invalid_finance_type'       => 'es_ The transaction finance type code is invalid.',
        'country_or_region'          => 'es_ You must add either recipient country or recipient region.',
        'country_region_in_activity' => 'es_ Recipient Region or Recipient Country is already added at activity level. You can add a Recipient Region and or Recipient Country either at activity level or at transaction level.',
        'sector_in_activity'         => 'es_ Sector has already been declared at activity level. You can’t declare a sector at the transaction level. To declare at transaction level, you need to remove sector at activity level.',
        'aid_type'                   => [
            'invalid_vocabulary'  => 'es_ The transaction aid type vocabulary is invalid.',
            'invalid_code'        => 'es_ The transaction aid type code is invalid.',
            'invalid_status_code' => 'es_ The transaction tied status code is invalid.',
        ],
        'date'                       => [
            'before' => 'es_ The @iso-date must not be in future.',
            'date'   => 'es_ The @iso-date field must be a valid date.',
        ],
        'value'                      => [
            'numeric_amount' => 'es_ The @amount field must be a number.',
            'date'           => [
                'before' => 'es_ The @value-date must not be in future.',
                'date'   => 'es_ The @value-date field must be a valid date.',
            ],
        ],
        'sector'                     => [
            'required'           => 'es_ You have declared sector at transaction level so you must declare sector for all the transactions.',
            'invalid_code'       => 'es_ The transaction sector code is invalid.',
            'vocabulary_uri_url' => 'es_ The transaction sector vocabulary-uri field must be a valid url.',
        ],
        'provider_org'               => [
            'invalid' => 'es_ The transaction provider org type is invalid.',
        ],
        'receiver_org'               => [
            'exclude_operators' => 'es_ The transaction receiver-activity-id field is not valid.',
            'invalid_type'      => 'es_ The transaction receiver org type is invalid.',
        ],
        'recipient_region'           => [
            'invalid_vocabulary'        => 'es_ The transaction recipient region vocabulary is invalid.',
            'invalid_region_code'       => 'es_ The transaction recipient region code is invalid.',
            'vocabulary_uri_url'        => 'es_ The transaction recipient region vocabulary uri must be a valid url.',
            'region_vocabulary_uri_url' => 'es_ The @vocabulary-uri field must be a valid url.',
        ],
        'recipient_country'          => [
            'invalid_code' => 'es_ The transaction recipient country code is invalid.',
        ],
    ],
    'activity_results'                 => [
        'result_id' => [
            'same_activity' => 'es_ All results must belong to the same activity.',
            'no_match'      => 'es_ Results IDs do not match the specified activity.',
            'unpublish'     => 'es_ Please unpublish activity before deleting results.',
        ],
        'reference' => [
            'vocabulary_uri_url' => 'es_ The @vocabulary-uri field must be a valid url.',
            'code_present'       => 'es_ The code is already defined in its indicators',
            'vocabulary_present' => 'es_ The vocabulary is already defined in its indicators',
        ],
    ],
    'activity_indicators'              => [
        'invalid_measure'            => 'es_ The indicator measure is invalid.',
        'invalid_aggregation_status' => 'es_ The indicator aggregation status is invalid.',
        'invalid_ascending'          => 'es_ The indicator ascending is invalid.',
        'reference'                  => [
            'uri_url'                       => 'es_ The @indicator-uri field must be a valid url.',
            'result_ref_code_present'       => 'es_ The code is already defined in its result',
            'result_ref_vocabulary_present' => 'es_ The vocabulary is already defined in its result',
        ],
        'baseline'                   => [
            'year'  => [
                'invalid_year' => 'es_ The @year field is not valid.',
                'in'           => 'es_ The @year field should be the year of baseline date',
                'digits'       => 'es_ The @year field must have 4 digits.',
            ],
            'value' => [
                'numeric' => 'es_ The @value field must be a number.',
                'gte'     => 'es_ The @value field must be greater or equal to 0.',
            ],
        ],
    ],
    'activity_periods'                 => [
        'date'  => [
            'date'             => 'es_ The @date field must be a proper date.',
            'after'            => 'es_ The @iso-date field of period end must be a date after @iso-field of period start',
            'gte_1900'         => 'es_ The @iso-date must be greater than 1900',
            'period_start_end' => 'es_ The @iso-date field of period end and @iso-date of period start must not have difference of more than a year',
        ],
        'value' => [
            'numeric'           => 'es_ The @value field must be numeric.',
            'qualitative_empty' => 'es_ Value must be omitted when the indicator measure is qualitative.',
            'target_required'   => 'es_ Target value is required if actual value is not provided.',
            'actual_required'   => 'es_ Actual value is required if target value is not provided.',
        ],
    ],
    'activity_upload'                  => [
        'required'          => 'es_ The activity file must be uploaded',
        'activity_file'     => 'es_ The file must be of either xml or csv format.',
        'max'               => 'es_ The file shouldn\'t be greater than 10MB.',
        'xls_required'      => 'es_ The xls file must be uploaded',
        'xls_activity_file' => 'es_ The file must be of xls format.',
    ],
    'organization_document_link'       => [
        'category_code' => [
            'unique'   => 'es_ The category @code field must be unique.',
            'required' => 'es_ The @code field is required.',
        ],
        'language'      => [
            'unique' => 'es_ The language @code field must be unique.',
        ],
    ],
    'value_date_required'              => 'es_ The @value-date field is required.',
    'value_date_date'                  => 'es_ The @value-date must be a date.',
    'value_date_after_or_equal'        => 'es_ The value date field must be a date between period start and period end',
    'value_date_with_value'            => 'es_ The @value-date is required with value.',
    'value_date_after_equal'           => 'es_ The @value-date field must be a date between period start and period end',
    'activity_not_exist'               => 'es_ Activity does not exist',
    'result_not_exist'                 => 'es_ Result does not exist',
    'transaction_not_exist'            => 'es_ Transaction does not exist',
    'indicator_not_exist'              => 'es_ Indicator does not exist',
    'period_not_exist'                 => 'es_ Period does not exist',
    'must_be_superadmin'               => 'es_ You need to be superadmin to use this route.',
];
