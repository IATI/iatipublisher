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

    'accepted'             => 'fr_ The :attribute must be accepted.',
    'accepted_if'          => 'fr_ The :attribute must be accepted when :other is :value.',
    'active_url'           => 'fr_ The :attribute is not a valid URL.',
    'after'                => 'fr_ The :attribute must be a date after :date.',
    'after_or_equal'       => 'fr_ The :attribute must be a date after or equal to :date.',
    'alpha'                => 'fr_ The :attribute must only contain letters.',
    'alpha_dash'           => 'fr_ The :attribute must only contain letters, numbers, dashes and underscores.',
    'alpha_num'            => 'fr_ The :attribute must only contain letters and numbers.',
    'array'                => 'fr_ The :attribute must be an array.',
    'before'               => 'fr_ The :attribute must be a date before :date.',
    'before_or_equal'      => 'fr_ The :attribute must be a date before or equal to :date.',
    'between'              => [
        'numeric' => 'fr_ The :attribute must be between :min and :max.',
        'file'    => 'fr_ The :attribute must be between :min and :max kilobytes.',
        'string'  => 'fr_ The :attribute must be between :min and :max characters.',
        'array'   => 'fr_ The :attribute must have between :min and :max items.',
    ],
    'boolean'              => 'fr_ The :attribute field must be true or false.',
    'confirmed'            => 'fr_ The :attribute confirmation does not match.',
    'current_password'     => 'fr_ The password is incorrect.',
    'date'                 => 'fr_ The :attribute is not a valid date.',
    'date_equals'          => 'fr_ The :attribute must be a date equal to :date.',
    'date_format'          => 'fr_ The :attribute does not match the format :format.',
    'declined'             => 'fr_ The :attribute must be declined.',
    'declined_if'          => 'fr_ The :attribute must be declined when :other is :value.',
    'different'            => 'fr_ The :attribute and :other must be different.',
    'digits'               => 'fr_ The :attribute must be :digits digits.',
    'digits_between'       => 'fr_ The :attribute must be between :min and :max digits.',
    'dimensions'           => 'fr_ The :attribute has invalid image dimensions.',
    'distinct'             => 'fr_ The :attribute field has a duplicate value.',
    'email'                => 'fr_ The :attribute must be a valid email address.',
    'ends_with'            => 'fr_ The :attribute must end with one of the following: :values.',
    'enum'                 => 'fr_ The selected :attribute is invalid.',
    'exists'               => 'fr_ The selected :attribute is invalid.',
    'file'                 => 'fr_ The :attribute must be a file.',
    'filled'               => 'fr_ The :attribute field must have a value.',
    'gt'                   => [
        'numeric' => 'fr_ The :attribute must be greater than :value.',
        'file'    => 'fr_ The :attribute must be greater than :value kilobytes.',
        'string'  => 'fr_ The :attribute must be greater than :value characters.',
        'array'   => 'fr_ The :attribute must have more than :value items.',
    ],
    'gte'                  => [
        'numeric' => 'fr_ The :attribute must be greater than or equal to :value.',
        'file'    => 'fr_ The :attribute must be greater than or equal to :value kilobytes.',
        'string'  => 'fr_ The :attribute must be greater than or equal to :value characters.',
        'array'   => 'fr_ The :attribute must have :value items or more.',
    ],
    'image'                => 'fr_ The :attribute must be an image.',
    'in'                   => 'fr_ The selected :attribute is invalid.',
    'in_array'             => 'fr_ The :attribute field does not exist in :other.',
    'integer'              => 'fr_ The :attribute must be an integer.',
    'ip'                   => 'fr_ The :attribute must be a valid IP address.',
    'ipv4'                 => 'fr_ The :attribute must be a valid IPv4 address.',
    'ipv6'                 => 'fr_ The :attribute must be a valid IPv6 address.',
    'json'                 => 'fr_ The :attribute must be a valid JSON string.',
    'lt'                   => [
        'numeric' => 'fr_ The :attribute must be less than :value.',
        'file'    => 'fr_ The :attribute must be less than :value kilobytes.',
        'string'  => 'fr_ The :attribute must be less than :value characters.',
        'array'   => 'fr_ The :attribute must have less than :value items.',
    ],
    'lte'                  => [
        'numeric' => 'fr_ The :attribute must be less than or equal to :value.',
        'file'    => 'fr_ The :attribute must be less than or equal to :value kilobytes.',
        'string'  => 'fr_ The :attribute must be less than or equal to :value characters.',
        'array'   => 'fr_ The :attribute must not have more than :value items.',
    ],
    'mac_address'          => 'fr_ The :attribute must be a valid MAC address.',
    'max'                  => [
        'numeric' => 'fr_ The :attribute must not be greater than :max.',
        'file'    => 'fr_ The :attribute must not be greater than :max kilobytes.',
        'string'  => 'fr_ The :attribute must not be greater than :max characters.',
        'array'   => 'fr_ The :attribute must not have more than :max items.',
    ],
    'mimes'                => 'fr_ The :attribute must be a file of type: :values.',
    'mimetypes'            => 'fr_ The :attribute must be a file of type: :values.',
    'min'                  => [
        'numeric' => 'fr_ The :attribute must be at least :min.',
        'file'    => 'fr_ The :attribute must be at least :min kilobytes.',
        'string'  => 'fr_ The :attribute must be at least :min characters.',
        'array'   => 'fr_ The :attribute must have at least :min items.',
    ],
    'multiple_of'          => 'fr_ The :attribute must be a multiple of :value.',
    'not_in'               => 'fr_ The selected :attribute is invalid.',
    'not_regex'            => 'fr_ The :attribute format is invalid.',
    'numeric'              => 'fr_ The :attribute must be a number.',
    'password'             => 'fr_ The password is incorrect.',
    'present'              => 'fr_ The :attribute field must be present.',
    'prohibited'           => 'fr_ The :attribute field is prohibited.',
    'prohibited_if'        => 'fr_ The :attribute field is prohibited when :other is :value.',
    'prohibited_unless'    => 'fr_ The :attribute field is prohibited unless :other is in :values.',
    'prohibits'            => 'fr_ The :attribute field prohibits :other from being present.',
    'regex'                => 'fr_ The :attribute format is invalid.',
    'required'             => 'fr_ The :attribute field is required.',
    'required_array_keys'  => 'fr_ The :attribute field must contain entries for: :values.',
    'required_if'          => 'fr_ The :attribute field is required when :other is :value.',
    'required_unless'      => 'fr_ The :attribute field is required unless :other is in :values.',
    'required_with'        => 'fr_ The :attribute field is required when :values is present.',
    'required_with_all'    => 'fr_ The :attribute field is required when :values are present.',
    'required_without'     => 'fr_ The :attribute field is required when :values is not present.',
    'required_without_all' => 'fr_ The :attribute field is required when none of :values are present.',
    'same'                 => 'fr_ The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'fr_ The :attribute must be :size.',
        'file'    => 'fr_ The :attribute must be :size kilobytes.',
        'string'  => 'fr_ The :attribute must be :size characters.',
        'array'   => 'fr_ The :attribute must contain :size items.',
    ],
    'starts_with'          => 'fr_ The :attribute must start with one of the following: :values.',
    'string'               => 'fr_ The :attribute must be a string.',
    'timezone'             => 'fr_ The :attribute must be a valid timezone.',
    'unique'               => 'fr_ The :attribute has already been taken.',
    'uploaded'             => 'fr_ The :attribute failed to upload.',
    'url'                  => 'fr_ The :attribute must be a valid URL.',
    'uuid'                 => 'fr_ The :attribute must be a valid UUID.',

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
            'rule-name' => 'fr_ custom-message',
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
    'sum'                                              => 'fr_ The sum of percentage within a vocabulary must add up to 100.',
    'overall_sum'                                      => 'fr_ The sum of percentage must not be more than 100.',
    'required_custom'                                  => 'fr_ :attribute is required when there are multiple codes.',
    'total'                                            => 'fr_ :attribute should be 100 when there is only one :values.',
    'csv_required'                                     => 'fr_ At row :number :attribute is required',
    'csv_unique'                                       => 'fr_ At row :number :attribute should be unique',
    'csv_invalid'                                      => 'fr_ At row :number :attribute is invalid',
    'csv_numeric'                                      => 'fr_ At row :number :attribute should be numeric',
    'csv_unique_validation'                            => 'fr_ At row :number :attribute is invalid and must be unique.',
    'csv_among'                                        => 'fr_ At row :number at least one :type among :attribute is required.',
    'csv_only_one'                                     => 'fr_ At row :number only one among :attribute is required.',
    'year_value_narrative_validation'                  => 'fr_ :year and :value is required if :narrative is not empty.',
    'year_narrative_validation'                        => 'fr_ :year is required if :narrative is not empty.',
    'org_required'                                     => 'fr_ At least one organisation name is required',
    'custom_unique'                                    => 'fr_ :attribute has already been taken.',
    'user_identifier_taken'                            => 'fr_ Sorry! this User Identifier is already taken',
    'enter_valid'                                      => 'fr_ Please enter valid :attribute',
    'sector_validation'                                => 'fr_ Sector must be present either at Activity or in all Transactions level.',
    'sector_narrative'                                 => 'fr_ <a href=\'%s\' >Sector Narrative</a> is required when vocabulay is 98 or 99.',
    'transaction_sector_narrative'                     => 'fr_ <a href=\'%s\' >Transaction Sector Narrative</a> is required when vocabulay is 98 or 99.',
    'transaction_sector_validation'                    => 'fr_ All Transactions must contain Sector element.',
    'sector_in_activity_and_transaction_remove'        => 'fr_ You can only mention Sector either at Activity or in Transaction level(should be included in all transactions) but not both. <br/>Please click the link to remove Sector From: <a href=\'%s\' class=\'delete_data\'>Transaction Level</a> OR <a href=\'%s\' class=\'delete_data\'>Activity Level</a>',
    'sector_in_activity_and_transaction'               => 'fr_ You need to mention either Recipient Country or Region either in Activity Level or in Transaction level. You can\'t have Country/Region in both Activity level and Transaction level. Also, they cannot be empty for both activity and transactions',
    'recipient_country_or_region_required'             => 'fr_ Either Recipient Country or Recipient Region is required in Activity Level or Transaction Level.',
    'transaction_recipient_country_or_region_required' => 'fr_ All Transactions must contain Recipient Region or Recipient Country',
    'sum_of_percentage'                                => 'fr_ The sum of percentage in :attribute must be 100.',
    'validation_before_completed'                      => 'fr_ Please make sure you enter the following fields before changing to completed state.',
    'reporting_org_identifier_unique'                  => 'fr_ This reporting organization identifier is being used by :orgName. This identifier has to be unique. Please contact us at support@aidstream.org',
    'code_list'                                        => 'fr_ :attribute is not valid.',
    'string'                                           => 'fr_ :attribute should be string',
    'negative'                                         => 'fr_ :attribute cannot be negative',
    'actual_date'                                      => 'fr_ Actual Start Date And Actual End Date must not exceed present date',
    'multiple_activity_date'                           => 'fr_ Multiple Activity dates are not allowed.',
    'start_end_date'                                   => 'fr_ Actual Start Date or Planned Start Date should be before Actual End Date or Planned End Date.',
    'csv_date'                                         => 'fr_ :attribute must be of format Y-m-d.',
    'multiple_values'                                  => 'fr_ Multiple :attribute are not allowed.',
    'csv_size'                                         => 'fr_ At least one :attribute is required',
    'multiple_narratives'                              => 'fr_ Multiple narratives for :attribute with the same type is not allowed.',
    'required_only_one_among'                          => 'fr_ Either :attribute or :values is required.',
    'recipient_country_region_percentage_sum'          => 'fr_ Sum of percentage of Recipient Country and Recipient Region must be equal to 100.',
    'invalid_in_transaction'                           => 'fr_ Entered :attribute is incorrect in Transaction.',
    'invalid_in_sector'                                => 'fr_ Entered :attribute is incorrect in Sector.',
    'required_if_in_transaction'                       => 'fr_ :attribute is required if :values is not present in Transaction.',
    'sector_vocabulary_required'                       => 'fr_ Sector Vocabulary is required in Transaction if not present in Activity Level.',
    'required_in_transaction'                          => 'fr_ :attribute is required in Transaction.',
    'invalid_language'                                 => 'fr_ Invalid :attribute language',
    'unique_lang'                                      => 'fr_ Repeated :attribute in the same language is not allowed.',
    'indicator_ascending'                              => 'fr_ Indicator Ascending should be true/false, 0/1 or Yes/No.',
    'indicator_size'                                   => 'fr_ Indicator Baseline Year or Value should occur once and no more than once within an Indicator.',
    'narrative_required'                               => 'fr_ :attribute Narrative is required.',
    'no_more_than_once'                                => 'fr_ :attribute should occur once and no more than once within :values.',
    'budget_period_end_date'                           => 'fr_ Budget Period End Date',
    'spaces_not_allowed'                               => 'fr_ You cannot enter spaces in organization name abbreviation.',
    'custom'                                           => [
        'attribute-name' => [
            'rule-name' => 'fr_ custom-message',
        ],
    ],
    'not_in_spam_emails'                               => 'fr_ This email has been flagged as spam and cannot be used.',
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
        'username'              => 'fr_ username',
        'full_name'             => 'fr_ full name',
        'email'                 => 'fr_ email',
        'publisher_id'          => 'fr_ publisher id',
        'publisher_name'        => 'fr_ publisher name',
        'identifier'            => 'fr_ identifier',
        'password'              => 'fr_ password',
        'password_confirmation' => 'fr_ password confirmation',
        'registration_agency'   => 'fr_ registration agency',
        'registration_number'   => 'fr_ registration number',
        'country'               => 'fr_ country',
        'default_language'      => 'fr_ default language',
        'publisher_type'        => 'fr_ publisher type',
        'license_id'            => 'fr_ license id',
        'description'           => 'fr_ description',
        'image_url'             => 'fr_ image url',
        'contact_email'         => 'fr_ contact email',
        'website'               => 'fr_ website',
        'source'                => 'fr_ source',
        'language_preference'   => 'fr_ language preference',
        'current_password'      => 'fr_ current password',
        'default_currency'      => 'fr_ default currency',
        'hierarchy'             => 'fr_ hierarchy',
        'budget_not_provided'   => 'fr_ budget not provided',
        'humanitarian'          => 'fr_ humanitarian',
        'api_token'             => 'fr_ api token',
        'narrative'             => 'fr_ narrative',
        'activity_identifier'   => 'fr_ activity identifier',
        'iati_identifier_text'  => 'fr_ iati identifier text',
    ],
    'within_a_year'                    => 'fr_ The :attribute must be within a year after :date.',
    'required_if_any'                  => 'fr_ The :field is required if any of the fields of :element are filled.',
    'amount'                           => 'fr_ Please enter amount in the format xx.xx',
    'greater'                          => 'fr_ The :attribute must be greater than or equal to :value',
    'date_greater_than'                => 'fr_ The date must be greater than :value',
    'logged_in_verify'                 => 'fr_ User must be logged in to verify email.',
    'attribute_exists'                 => 'fr_ The :attribute already exists.',
    'narrative_required_with_lang'     => 'fr_ The narrative is required when language is specified.',
    'narrative_required_with_xml_lang' => 'fr_ The narrative field is required with @xml:lang field.',
    'title_unique_lang'                => 'fr_ The title language field must be unique.',
    'first_title_required'             => 'fr_ The first title is required.',
    'xml_lang_unique'                  => 'fr_ The @xml:lang field must be unique.',
    'narrative_language_unique'        => 'fr_ The narrative language field must be unique.',
    'narrative_with_language'          => 'fr_ The narrative field is required with language field.',
    'xml_lang_invalid'                 => 'fr_ The @xml:lang field is invalid.',
    'narrative_is_required'            => 'fr_ The Narrative field is required.',
    'period_end_date'                  => 'fr_ Period end must be a date.',
    'period_end_gt_1900'               => 'fr_ Period end date must be date greater than year 1900.',
    'period_end_required'              => 'fr_ Period end is a required field',
    'period_end_after'                 => 'fr_ Period end must be a date after period',
    'document_link_format_invalid'     => 'fr_ The document link format is invalid',
    'url_valid'                        => 'fr_ The @url field must be a valid url.',
    'document_link_category_unique'    => 'fr_ The document link category code field must be a unique.',
    'document_link_category_invalid'   => 'fr_ The document link category code is invalid.',
    'document_link_language_unique'    => 'fr_ The document link language code field must be a unique.',
    'document_link_language_invalid'   => 'fr_ The document link language code is invalid.',
    'iso_proper_date'                  => 'fr_ The @iso-date field must be a proper date.',
    'iso_gt_1900'                      => 'fr_ The @iso-date field must be a greater than 1900.',
    'iso_date_required'                => 'fr_ The @iso-date field is required.',
    'iso_date_after'                   => 'fr_ The @iso-date field must be a date after period-start date.',
    'period_longer'                    => 'fr_ The period must not be longer than one year.',
    'description_type_invalid'         => 'fr_ The selected description type is invalid.',
    'vocabulary_uri_url'               => 'fr_ The @vocabulary-uri field must be a valid url.',
    'name_narrative_required'          => 'fr_ The narrative is required.',
    'registration_number_regex'        => 'fr_ The registration_number format is invalid.',
    'invalid_currency'                 => 'fr_ The value currency is invalid.',
    'invalid_ref'                      => 'fr_ The @ref format is invalid.',
    'reporting_org_ref_must_match'     => 'fr_ The @ref of reporting-org must match the organisation-identifier.',
    'language_required_with_narrative' => 'fr_ The language field is required when narrative field is present.',
    'amount_required'                  => 'fr_ The amount field is required.',
    'amount_numeric'                   => 'fr_ The amount must be numeric.',
    'amount_min'                       => 'fr_ The amount must not be in negative.',
    'amount_with_value'                => 'fr_ The amount field is required with value.',
    'amount_number'                    => 'fr_ The amount field must be a number.',
    'amount_negative'                  => 'fr_ The amount field must not be in negative.',
    'username_regex'                   => 'fr_ The username is invalid. Username must be purely lowercase alphabets followed by alphanumeric(ascii) characters and these symbols:-_',
    'email_unique'                     => 'fr_ Email is already in use in IATI Publisher.',
    'other_identifier'                 => [
        'regex'        => 'fr_ The other identifier reference field shouldn\'t contain the symbols /, &, | or ?.',
        'type_invalid' => 'fr_ The other identifier type is not valid.',
        'owner_org'    => [
            'reference_regex' => 'fr_ The owner org reference field shouldn\'t contain the symbols /, &, | or ?.',
        ]
    ],
    'activity_status'                  => [
        'in'   => 'fr_ The activity status does not exist.',
        'size' => 'fr_ The activity status cannot have more than one value.',
    ],
    'activity_date'                    => [
        'invalid'              => 'fr_ Date is invalid.',
        'date_before'          => 'fr_ Actual start and end dates may not be in the future.',
        'end_later_than_start' => 'fr_ End date must be later than the start date.',
        'invalid_type'         => 'fr_ The selected type is invalid.',
    ],
    'activity_scope'                   => [
        'in'   => 'fr_ The activity scope does not exist.',
        'size' => 'fr_ The activity scope cannot have more than one value.',
    ],
    'activity_recipient_country'       => [
        'already_in_transaction' => 'fr_ Recipient Country is already added at transaction level. You can add a Recipient Country either at activity level or at transaction level but not at both.',
        'invalid_code'           => 'fr_ The recipient country code is invalid.',
        'duplicate_country_code' => 'fr_ The Country Code cannot be redundant.',
        'percentage'             => [
            'numeric'                    => 'fr_ The recipient country percentage must be a number.',
            'max'                        => 'fr_ The recipient country percentage cannot be greater than 100',
            'sum_exceeded'               => 'fr_ The sum of recipient country percentage cannot be greater than 100',
            'min'                        => 'fr_ The recipient country percentage must be at least 0.',
            'region_percentage_complete' => 'fr_ Recipient Region’s percentage is already 100%. The sum of the percentages of Recipient Country and Recipient Region must be 100%.',
            'allocated_country_percent'  => 'fr_ The sum of percentages of Recipient Region(s) and Recipient country must be 100%',
        ],
    ],
    'activity_recipient_region'        => [
        'already_in_transaction' => 'fr_ Recipient Region is already added at transaction level. You can add a Recipient Region either at activity level or at transaction level but not at both.',
        'invalid_vocabulary'     => 'fr_ The recipient region vocabulary is invalid.',
        'invalid_code'           => 'fr_ The recipient region code is invalid.',
        'vocabulary_uri_url'     => 'fr_ The recipient region vocabulary uri must be a valid url.',
        'percentage'             => [
            'numeric'                                => 'fr_ The recipient region percentage field must be a number.',
            'country_percentage_complete'            => 'fr_ Recipient Country’s percentage is already 100%. The sum of the percentages of Recipient Country and Recipient Region must be 100%',
            'in'                                     => 'fr_ The sum of the percentages of Recipient Country(ies) and Recipient Region(s) must always be 100%',
            'allocated_region_total_mismatch'        => 'fr_ The sum of recipient country and recipient region of a specific vocabulary must be 100%',
            'sum_greater_than'                       => 'fr_ Sum of percentage within vocabulary cannot be greater than 100',
            'percentage_within_vocabulary'           => 'fr_ The sum of percentage of Recipient Country and Recipient Regions (within the same Region Vocabulary) must be equal to 100%',
            'min'                                    => 'fr_ The recipient country percentage must be at least 0.',
            'single_allocated_region_total_mismatch' => 'fr_ The sum of the percentages of Recipient Country(ies) and Recipient Region(s) must always be 100%',
        ],
    ],
    'activity_sector'                  => [
        'already_in_transactions' => 'fr_ Sector has already been declared at transaction level. You can’t declare a sector at the activity level.',
        'invalid_vocabulary'      => 'fr_ The sector vocabulary is invalid.',
        'invalid_code'            => 'fr_ The sector code is invalid.',
        'percentage'              => [
            'numeric'              => 'fr_ The percentage must be 100% or left empty, which is assumed as 100%.',
            'sector_total_percent' => 'fr_ The sum of percentages of same vocabulary must be equal to 100%',
        ],
    ],
    'activity_tag'                     => [
        'invalid_vocabulary'       => 'fr_ The tag vocabulary is invalid.',
        'invalid_sdg_code'         => 'fr_ The tag SDG code is invalid',
        'invalid_sdg_targets_code' => 'fr_ The tag SDG targets code is invalid.',
    ],
    'activity_policy_marker'           => [
        'invalid_significance' => 'fr_ The policy marker significance is invalid.',
        'invalid_code'         => 'fr_ The policy marker code is invalid.',
        'narrative_required'   => 'fr_ The narrative field is required when vocabulary is reporting organisation.'
    ],
    'activity_collaboration_type'      => [
        'in'   => 'fr_ The collaboration type does not exist.',
        'size' => 'fr_ The collaboration type cannot have more than one value.',
    ],
    'activity_default_flow_type'       => [
        'in'   => 'fr_ The default flow type does not exist.',
        'size' => 'fr_ The default flow type cannot have more than one value.',
    ],
    'activity_default_finance_type'    => [
        'in'   => 'fr_ The default finance type does not exist.',
        'size' => 'fr_ The default finance type cannot have more than one value.',
    ],
    'activity_default_aid_type'        => [
        'invalid_vocabulary'                  => 'fr_ The default aid type vocabulary is invalid.',
        'invalid'                             => 'fr_ The default aid type is invalid.',
        'invalid_earmarking_category'         => 'fr_ The default aid type earmarking category is invalid.',
        'invalid_earmarking_modality'         => 'fr_ The default aid type earmarking modality is invalid.',
        'invalid_cash_and_voucher_modalities' => 'fr_ The default aid type cash and voucher modalities is invalid.',
    ],
    'activity_default_tied_status'     => [
        'in'   => 'fr_ The default tied status does not exist.',
        'size' => 'fr_ The default tied status cannot have more than one value.',
    ],
    'activity_country_budget_items'    => [
        'invalid_vocabulary' => 'fr_ The country budget item vocabulary is invalid.',
        'invalid_code'       => 'fr_ The budget item code in invalid.',
        'percentage'         => [
            'numeric' => 'fr_ The budget item percentage field must be a number.',
            'max'     => 'fr_ The budget item percentage field cannot be greater than 100.',
            'sum'     => 'fr_ The sum of percentage with budget items must add up to 100.',
            'total'   => 'fr_ The budget item percentage field should be 100 when there is only one budget item.'
        ],
    ],
    'activity_humanitarian_scope'      => [
        'invalid_code'       => 'fr_ The humanitarian scope type is invalid.',
        'invalid_vocabulary' => 'fr_ The humanitarian scope vocabulary is invalid.',
        'code_string'        => 'fr_ The humanitarian scope code must be a string.',
        'vocabulary_uri_url' => 'fr_ The humanitarian scope vocabulary-uri must be a proper url.',
    ],
    'activity_capital_spend'           => [
        'numeric' => 'fr_ The capital spend must be a number',
        'between' => 'fr_ The capital spend must be a number between 0 and 100',
        'size'    => 'fr_ The capital spend cannot have more than one value.',
    ],
    'activity_related_activity'        => [
        'invalid_relationship_type' => 'fr_ The relationship type in related activity is invalid.',
    ],
    'activity_conditions'              => [
        'invalid_type' => 'fr_ The condition type is invalid.',
    ],
    'activity_contact_info'            => [
        'telephone' => [
            'numeric' => 'fr_ The contact info telephone number must be valid numeric value.',
            'regex'   => 'fr_ The contact info telephone number is invalid.',
            'min'     => 'fr_ The contact info telephone number must have atleast 7 digits.',
            'max'     => 'fr_ The contact info telephone number must not have more than 20 digits.',
        ],
        'email'     => [
            'valid'          => 'fr_ The contact info email must be valid.',
            'invalid_format' => 'fr_ The contact info email format is invalid.',
        ],
        'website'   => [
            'invalid_url' => 'fr_ The contact info website url must be valid url.',
        ],
    ],
    'activity_location'                => [
        'invalid_reach_code'          => 'fr_ The location reach code is invalid.',
        'invalid_location_exactness'  => 'fr_ The location exactness is invalid.',
        'invalid_location_class'      => 'fr_ The location class is invalid.',
        'invalid_feature_designation' => 'fr_ The location feature designation is invalid.',
        'invalid_vocabulary'          => 'fr_ The location id vocabulary is invalid.',
        'administrative'              => [
            'invalid_vocabulary' => 'fr_ The location administrative vocabulary is invalid.',
            'invalid_code'       => 'fr_ The location administrative code is invalid.',
            'level_min'          => 'fr_ The location administrative level must not have negative value.',
            'level_int'          => 'fr_ The location administrative level must be an integer.',
        ],
        'point'                       => [
            'latitude_numeric'  => 'fr_ The pos latitude must be numeric',
            'longitude_numeric' => 'fr_ The pos longitude must be numeric',
        ],
    ],
    'activity_planned_disbursement'    => [
        'invalid_type' => 'fr_ The planned disbursement type is invalid.',
        'provider_org' => [
            'invalid_type' => 'fr_ The planned disbursement provider org type is invalid.',
            'regex'        => 'fr_ The planned disbursement provider org ref shouldn\'t contain the symbols /, &, | or ?.',
        ],
        'receiver_org' => [
            'invalid_type' => 'fr_ The planned disbursement receiver org type is invalid.',
            'regex'        => 'fr_ The planned disbursement receiver org ref shouldn\'t contain the symbols /, &, | or ?.',
        ],
        'value'        => [
            'amount' => [
                'required' => 'fr_ Amount field is required',
                'numeric'  => 'fr_ Amount field must be a number',
                'min'      => 'fr_ Amount field must not be in negative.'
            ],
            'date'   => [
                'required'     => 'fr_ Value date is a required field',
                'invalid_date' => 'fr_ The Value Date must be a valid Date',
            ],
        ],
        'period_end'   => [
            'date'           => 'fr_ Period end must be a date field.',
            'gt_1900'        => 'fr_ Period end date must be date greater than year 1900.',
            'required'       => 'fr_ Period end is a required field',
            'after_or_equal' => 'fr_ Period end must be a date after period',
        ],
        'period_start' => [
            'date' => 'fr_ Period Start must be a date field.',
        ],
        'date'         => [
            'period_start_end' => 'fr_ The Planned Disbursement Period must not be longer than three months',
        ],
    ],
    'activity_participating_org'       => [
        'invalid_identifier'       => 'fr_ The identifier must not contain symbols or blank space',
        'invalid_role'             => 'fr_ The participating organisation role is invalid.',
        'invalid_type'             => 'fr_ The participating organisation type is invalid.',
        'reference_required'       => 'fr_ The reference field is required when name is empty.',
        'invalid_crs_channel_code' => 'fr_ The Crs Channel Code is invalid.',
        'name_required'            => 'fr_ The name field is required when participating org reference is empty.',
    ],
    'activity_budget'                  => [
        'budget'     => [
            'budgets_identical' => 'fr_ The periods of multiple budgets with the same type should not be the same',
            'invalid_type'      => 'fr_ The budget type is invalid.',
            'invalid_status'    => 'fr_ The budget status is invalid.',
        ],
        'period_end' => [
            'before' => 'fr_ The Period End iso-date must be within a year after Period Start iso-date.',
        ],
        'date'       => [
            'date'             => 'fr_ The iso-date field must be a valid date.',
            'period_start_end' => 'fr_ The Budget Period must not be longer than one year',
            'gt_1900'          => 'fr_ The iso-date field must be date after year 1900.',
            'after'            => 'fr_ The Period End iso-date must be a date after Period Start iso-date',
        ],
        'value'      => [
            'date' => 'fr_ The value-date field must be a valid date.',
        ],
    ],
    'activity_transactions'            => [
        'transaction_id'             => [
            'same_activity' => 'fr_ All transactions must belong to the same activity.',
            'mismatch'      => 'fr_ Transaction IDs do not match the specified activity.',
            'unpublish'     => 'fr_ Please unpublish activity before deleting transactions.',
        ],
        'invalid_type'               => 'fr_ The transaction type is invalid.',
        'invalid_flow_type'          => 'fr_ The transaction flow type code is invalid.',
        'invalid_finance_type'       => 'fr_ The transaction finance type code is invalid.',
        'country_or_region'          => 'fr_ You must add either recipient country or recipient region.',
        'country_region_in_activity' => 'fr_ Recipient Region or Recipient Country is already added at activity level. You can add a Recipient Region and or Recipient Country either at activity level or at transaction level.',
        'sector_in_activity'         => 'fr_ Sector has already been declared at activity level. You can’t declare a sector at the transaction level. To declare at transaction level, you need to remove sector at activity level.',
        'aid_type'                   => [
            'invalid_vocabulary'  => 'fr_ The transaction aid type vocabulary is invalid.',
            'invalid_code'        => 'fr_ The transaction aid type code is invalid.',
            'invalid_status_code' => 'fr_ The transaction tied status code is invalid.',
        ],
        'date'                       => [
            'before' => 'fr_ The @iso-date must not be in future.',
            'date'   => 'fr_ The @iso-date field must be a valid date.',
        ],
        'value'                      => [
            'numeric_amount' => 'fr_ The @amount field must be a number.',
            'date'           => [
                'before' => 'fr_ The @value-date must not be in future.',
                'date'   => 'fr_ The @value-date field must be a valid date.',
            ],
        ],
        'sector'                     => [
            'required'           => 'fr_ You have declared sector at transaction level so you must declare sector for all the transactions.',
            'invalid_code'       => 'fr_ The transaction sector code is invalid.',
            'vocabulary_uri_url' => 'fr_ The transaction sector vocabulary-uri field must be a valid url.',
        ],
        'provider_org'               => [
            'invalid' => 'fr_ The transaction provider org type is invalid.',
        ],
        'receiver_org'               => [
            'exclude_operators' => 'fr_ The transaction receiver-activity-id field is not valid.',
            'invalid_type'      => 'fr_ The transaction receiver org type is invalid.',
        ],
        'recipient_region'           => [
            'invalid_vocabulary'        => 'fr_ The transaction recipient region vocabulary is invalid.',
            'invalid_region_code'       => 'fr_ The transaction recipient region code is invalid.',
            'vocabulary_uri_url'        => 'fr_ The transaction recipient region vocabulary uri must be a valid url.',
            'region_vocabulary_uri_url' => 'fr_ The @vocabulary-uri field must be a valid url.',
        ],
        'recipient_country'          => [
            'invalid_code' => 'fr_ The transaction recipient country code is invalid.',
        ],
    ],
    'activity_results'                 => [
        'result_id' => [
            'same_activity' => 'fr_ All results must belong to the same activity.',
            'no_match'      => 'fr_ Results IDs do not match the specified activity.',
            'unpublish'     => 'fr_ Please unpublish activity before deleting results.',
        ],
        'reference' => [
            'vocabulary_uri_url' => 'fr_ The @vocabulary-uri field must be a valid url.',
            'code_present'       => 'fr_ The code is already defined in its indicators',
            'vocabulary_present' => 'fr_ The vocabulary is already defined in its indicators',
        ],
    ],
    'activity_indicators'              => [
        'invalid_measure'            => 'fr_ The indicator measure is invalid.',
        'invalid_aggregation_status' => 'fr_ The indicator aggregation status is invalid.',
        'invalid_ascending'          => 'fr_ The indicator ascending is invalid.',
        'reference'                  => [
            'uri_url'                       => 'fr_ The @indicator-uri field must be a valid url.',
            'result_ref_code_present'       => 'fr_ The code is already defined in its result',
            'result_ref_vocabulary_present' => 'fr_ The vocabulary is already defined in its result',
        ],
        'baseline'                   => [
            'year'  => [
                'invalid_year' => 'fr_ The @year field is not valid.',
                'in'           => 'fr_ The @year field should be the year of baseline date',
                'digits'       => 'fr_ The @year field must have 4 digits.',
            ],
            'value' => [
                'numeric' => 'fr_ The @value field must be a number.',
                'gte'     => 'fr_ The @value field must be greater or equal to 0.',
            ],
        ],
    ],
    'activity_periods'                 => [
        'date'  => [
            'date'             => 'fr_ The @date field must be a proper date.',
            'after'            => 'fr_ The @iso-date field of period end must be a date after @iso-field of period start',
            'gte_1900'         => 'fr_ The @iso-date must be greater than 1900',
            'period_start_end' => 'fr_ The @iso-date field of period end and @iso-date of period start must not have difference of more than a year',
        ],
        'value' => [
            'numeric'           => 'fr_ The @value field must be numeric.',
            'qualitative_empty' => 'fr_ Value must be omitted when the indicator measure is qualitative.',
            'target_required'   => 'fr_ Target value is required if actual value is not provided.',
            'actual_required'   => 'fr_ Actual value is required if target value is not provided.',
        ],
    ],
    'activity_upload'                  => [
        'required'          => 'fr_ The activity file must be uploaded',
        'activity_file'     => 'fr_ The file must be of either xml or csv format.',
        'max'               => 'fr_ The file shouldn\'t be greater than 10MB.',
        'xls_required'      => 'fr_ The xls file must be uploaded',
        'xls_activity_file' => 'fr_ The file must be of xls format.',
    ],
    'organization_document_link'       => [
        'category_code' => [
            'unique'   => 'fr_ The category @code field must be unique.',
            'required' => 'fr_ The @code field is required.',
        ],
        'language'      => [
            'unique' => 'fr_ The language @code field must be unique.',
        ],
    ],
    'value_date_required'              => 'fr_ The @value-date field is required.',
    'value_date_date'                  => 'fr_ The @value-date must be a date.',
    'value_date_after_or_equal'        => 'fr_ The value date field must be a date between period start and period end',
    'value_date_with_value'            => 'fr_ The @value-date is required with value.',
    'value_date_after_equal'           => 'fr_ The @value-date field must be a date between period start and period end',
    'activity_not_exist'               => 'fr_ Activity does not exist',
    'result_not_exist'                 => 'fr_ Result does not exist',
    'transaction_not_exist'            => 'fr_ Transaction does not exist',
    'indicator_not_exist'              => 'fr_ Indicator does not exist',
    'period_not_exist'                 => 'fr_ Period does not exist',
    'must_be_superadmin'               => 'fr_ You need to be superadmin to use this route.',
];
