<?php

return [
    // Commons
    'commons'             => [
        'next_step'               => 'es_ Next Step',
    ],
    'not_registered_page' => [
        'heading'             => 'es_ Create IATI Publisher Account and IATI Registry Account',
        'start_journey'       => 'es_ Start your IATI publishing journey by creating accounts in both IATI Publisher and the IATI Registry',
        'step_count_out_of_5' => 'es_ Step :count out of 5',
        'register_section'    => [
            // 1st Form
            'publisher_information_description'            => 'es_ This information will be used to register your organisation as an IATI publisher',
            'publisher_information_hover_text'             => 'es_ We refer to organisations who publish IATI data as \'Publishers\'. Before publishing data, all organisations need their own \'Publisher Account\' on the IATI Registry (iatiregistry.org). Enter your organisation\'s data here and we\'ll create your organisation\'s Publisher Account for you. These details will also be saved here in IATI Publisher.',
            'publisher_name_placeholder'                   => 'es_ Type your organisation name here',
            'publisher_name_hover_text'                    => 'es_ The name of your organisation publishing the data.',
            'publisher_id_hover_text'                      => 'es_ Provide a unique ID for your organisation. It must be at least two characters long and use lower case letters. You can include letters, numbers and also - (dash) and _ (underscore). Where possible use a short abbreviation of your organisation\'s name, for example: \'nef_mali\' for Near East Foundation Mali.',
            'organisation_registration_agency_hover_text'  => 'es_ Select the agency in your country where your organisation is registered. If you do not know this information please email <a href=\'mailto:support@iatistandard.org\' target=\'_blank\'>support@iatistandard.org</a>',
            'registration_number'                          => 'es_ Registration Number',
            'registration_number_hover_text'               => 'es_ Provide the registration number for your organisation that has been provided by organisation registration agency. If you do not know this please email <a href=\'mailto:support@iatistandard.org\' target=\'_blank\'>support@iatistandard.org</a>.',
            'identifier_hover_text'                        => 'es_ The Organisation Identifier is a unique code for your organisation. This is genereated from the Organisation Registration Agency and Registration Number. For more information read:  <a href="http://iatistandard.org/en/guidance/preparing-organisation/organisation-account/how-to-create-your-iati-organisation-identifier/" target="_blank">How to create your IATI organisation identifier.</a>',
            'organisation_type'                            => 'es_ Organisation Type',
            'organisation_type_placeholder'                => 'es_ Select an organisation type',
            'organisation_type_hover_text'                 => 'es_ Select the type that best describes your organisation.  <a href="https://iatistandard.org/en/iati-standard/203/codelists/organisationtype/" target="_blank"> Read more on Organisation types.</a>',
            'data_licence'                                 => 'es_ Data Licence',
            'data_licence_placeholder'                     => 'es_ Select a Data Licence',
            'data_licence_hover_text'                      => 'es_ Select the License under which your data is being published. IATI is an open data standard and requires you to make your data available under an open licence so it can be freely used. One of the most frequently used licenses is Creative Commons Attribution. <a href=\'https://iatistandard.org/en/guidance/standard-overview/preparing-your-organisation-data-publication/how-to-license-your-data/\' target=\'_blank\' > For more information read: How to license your data.</a>',
            'publisher_logo_url'                           => 'es_ Publisher Logo URL',
            'publisher_logo_url_placeholder'               => 'es_ E.g. http://mylogo.com ',
            'publisher_logo_url_hover_text'                => 'es_ Provide a link to an image to your organisation\'s logo (Optimum size: 200 x 120 px)',
            'organisation_description'                     => 'es_ Organisation Description',
            'organisation_description_placeholder'         => 'es_ Type description here',
            'organisation_description_hover_text'          => 'es_ Provide a short description about your organisation.',
            // 2nd Form
            'heading_two'                                  => 'es_ Contact Information',
            'contact_information_description'              => 'es_ This is your organisation\'s contact information',
            'contact_email'                                => 'es_ Contact Email',
            'contact_hover_text'                           => 'es_ Please add a contact email address for your organisation. Please note that IATI is an open data standard and the email provided here will be visible to others on the IATI Registry.',
            'website'                                      => 'es_ Website',
            'website_placeholder'                          => 'es_ E.g. http://mywebsite.com',
            'website_hover_text'                           => 'es_ Add the URL to your organisation\'s website.',
            'address'                                      => 'es_ Address',
            'address_placeholder'                          => 'es_ Type address here',
            'address_hover_text'                           => 'es_ Provide a contact address for your organisation.',
            // 3rd Form
            'heading_three'                                => 'es_ Publishing Additional Information',
            'publishing_description'                       => 'es_ This is about how your organisation will publish data',
            'source'                                       => 'es_ Source',
            'source_placeholder'                           => 'es_ Select a source',
            'source_hover_text'                            => 'es_ Select an option:<br>Primary - your organisation is publishing its own or (associated organisations\') data <br>Secondary - your organisation is reproducing data on the activities of another organisation',
            'record_exclusions'                            => 'es_ Record Exclusions',
            'record_exclusions_placeholder'                => 'es_ Type Record Exclusions here',
            'record_exclusions_hover_text'                 => 'es_ Does your organisation have an exclusion policy that provide details on what data that it cannot publish? For example an organisation may not be able to publish data because of political sensitivity issues or if information is commercially restricted. Please provide details here about what data your organisation needs to exclude (if any), and a URL to your organisation\'s exclusion policy (if it has one).<a href=\'https://iatistandard.org/en/guidance/standard-overview/preparing-your-organisation-data-publication/information-and-data-you-cant-publish-exclusions/\' target=\'_blank\'> For more information read: Information and data you can\'t publish (exclusions)</a>',
            // 4th Form
            'administrator_description'                    => 'es_ This will create an admin account for you as an individual',
            'password_help_text'                           => 'es_ Minimum length: 8 characters',
            'confirm_password_help_text'                   => 'es_ This should match the password on the left',
            // 5th Form
        ],

        'verification_email' => [
            'please_verify_your_account'  => 'es_ Please verify your account',
            'verification_email_sent_to'  => 'es_ A verification email has been sent to',
            'please_click_link_to_verify' => 'es_ Please click on the link in the email to verify and activate your new IATI publisher account.',
        ],
    ],
    'registered_page'     => [
        'heading'             => 'es_ Create IATI Publisher Account',
        'start_journey'       => 'es_ Start your IATI publishing journey by creating an account in IATI Publisher',
        'step_count_out_of_3' => 'es_ Step :count out of 3',
        'register_section'    => [
            // Form 1
            'publisher_information_description'            => 'es_ This information will be used to register your organisation',
            'publisher_information_hover_text'             => 'es_ Provide information about your organisation. You will need to provide the same information that you used to create your Publisher Account on the IATI Registry (iatiregistry.org).',
            'publisher_name'                               => 'es_ Publisher Name',
            'publisher_name_placeholder'                   => 'es_ Type your Publisher Name here',
            'publisher_name_hover_text'                    => 'es_ Provide the name of your organisation.',
            'publisher_id_hover_text'                      => 'es_ This is the unique ID for your organisation that you created when you set up your IATI Registry Publisher Account. It should be a shorter version of your organisation\'s name, which will include lowercase letters and may include numbers, - (dash) or _ (underscore). For example nef_mali\' for Near East Foundation Mali.',
            'organisation_registration_agency_hover_text'  => 'es_ Provide the name of the agency in your country where you organisation is registered. If you do not know this information please email support@iatistandard.org.',
            'organisation_registration_number'             => 'es_ Organisation Registration Number',
            'organisation_registration_number_hover_text'  => 'es_ Add the registration number for your organisation that has been provided by the registration agency named above.',
            'iati_organisation_identifier'                 => 'es_ IATI Organisation Identifier',
            'iati_organisation_identifier_hover_text'      => 'es_ The Organisation Identifier is a unique code for your organisation. This is generated from the Organisation Registration Agency and Registration Number. For more information read: <a href="http://iatistandard.org/en/guidance/preparing-organisation/organisation-account/how-to-create-your-iati-organisation-identifier/" target="_blank">How to create your IATI organisation identifier.</a>',
            // Form 2
            'administrator_description'                    => 'es_ This information will be used to create an admin account in IATI Publisher',
            'administrator_hover_text'                     => 'es_ Provide your information to create an admin account here on IATI Publisher.',
            // Error
            'info_doesnt_match_iati_registry'              => 'es_ Sorry, the information you provided doesn’t match your IATI Registry information.',
            'make_sure_account_holder'                     => 'es_ Please note that if you\’re an account holder in, <a href="https://iatiregistry.org/">IATI Registry</a>, make sure your <span class="font-bold">Publisher Name, Publisher ID and IATI Organisation ID</span> match your IATI Registry Information. Contact <a class="text-bluecoral" href="mailto:support@iatistandard.org">support@iatistandard.org</a>',

        ],
    ],
];
