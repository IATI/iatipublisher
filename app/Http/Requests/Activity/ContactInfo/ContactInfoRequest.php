<?php

declare(strict_types=1);

namespace App\Http\Requests\Activity\ContactInfo;

use App\Http\Requests\Activity\ActivityBaseRequest;
use App\Rules\ValidPhoneNumber;
use Illuminate\Support\Arr;

/**
 * Class ContactInfoRequest.
 */
class ContactInfoRequest extends ActivityBaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $data = $this->get('contact_info');
        $totalRules = [
            $this->getWarningForContactInfo($data),
            $this->getErrorsForContactInfo($data),
        ];

        return mergeRules($totalRules);
    }

    /**
     * Returns rules for contact info.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getWarningForContactInfo(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $contactInfoIndex => $contactInfo) {
            $contactInfoForm = sprintf('contact_info.%s', $contactInfoIndex);

            $tempRules = [
                $this->getWarningForDepartment(Arr::get($contactInfo, 'department', []), $contactInfoForm),
                $this->getWarningForOrganisation(Arr::get($contactInfo, 'organisation', []), $contactInfoForm),
                $this->getWarningForPersonName(Arr::get($contactInfo, 'person_name', []), $contactInfoForm),
                $this->getWarningForJobTitle(Arr::get($contactInfo, 'job_title', []), $contactInfoForm),
                $this->getWarningForMailingAddress(
                    Arr::get($contactInfo, 'mailing_address', []),
                    $contactInfoForm
                ),
            ];

            foreach ($tempRules as $tempRule) {
                foreach ($tempRule as $idx => $rule) {
                    $rules[$idx] = $rule;
                }
            }
        }

        return $rules;
    }

    /**
     * Returns rules for contact info.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getErrorsForContactInfo(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $contactInfoIndex => $contactInfo) {
            $contactInfoForm = sprintf('contact_info.%s', $contactInfoIndex);
            $rules[sprintf('%s.type', $contactInfoForm)] = 'nullable|in:' . implode(
                ',',
                array_keys(
                    $this->getCodeListForRequestFiles('ContactType', 'Activity', false)
                )
            );
            $tempRules = [
                $this->getErrorsForDepartment(Arr::get($contactInfo, 'department', []), $contactInfoForm),
                $this->getErrorsForOrganisation(Arr::get($contactInfo, 'organisation', []), $contactInfoForm),
                $this->getErrorsForPersonName(Arr::get($contactInfo, 'person_name', []), $contactInfoForm),
                $this->getErrorsForJobTitle(Arr::get($contactInfo, 'job_title', []), $contactInfoForm),
                $this->getErrorsForMailingAddress(Arr::get($contactInfo, 'mailing_address', []), $contactInfoForm),
                $this->getErrorsForTelephone(Arr::get($contactInfo, 'telephone', []), $contactInfoForm),
                $this->getErrorsForEmail(Arr::get($contactInfo, 'email', []), $contactInfoForm),
                $this->getErrorsForWebsite(Arr::get($contactInfo, 'website', []), $contactInfoForm),
            ];

            foreach ($tempRules as $tempRule) {
                foreach ($tempRule as $idx => $rule) {
                    $rules[$idx] = $rule;
                }
            }
        }

        return $rules;
    }

    /**
     * Returns rules for department.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getWarningForDepartment($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $departmentIndex => $department) {
            $departmentForm = sprintf('%s.department.%s', $formBase, $departmentIndex);

            foreach (
                $this->getWarningForNarrative(
                    $department['narrative'],
                    $departmentForm
                ) as $departmentNarrativeIndex => $departmentNarrativeRules
            ) {
                $rules[$departmentNarrativeIndex] = $departmentNarrativeRules;
            }
        }

        return $rules;
    }

    /**
     * Returns rules for organisation.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getWarningForOrganisation($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $organisationIndex => $organisation) {
            $organisationForm = sprintf('%s.organisation.%s', $formBase, $organisationIndex);

            foreach (
                $this->getWarningForNarrative(
                    $organisation['narrative'],
                    $organisationForm
                ) as $organisationNarrativeIndex => $organisationNarrativeRules
            ) {
                $rules[$organisationNarrativeIndex] = $organisationNarrativeRules;
            }
        }

        return $rules;
    }

    /**
     * Returns rules for person name.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getWarningForPersonName($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $personNameIndex => $personName) {
            $personNameForm = sprintf('%s.person_name.%s', $formBase, $personNameIndex);

            foreach (
                $this->getWarningForNarrative(
                    $personName['narrative'],
                    $personNameForm
                ) as $personNameNarrativeIndex => $personNameNarrativeRules
            ) {
                $rules[$personNameNarrativeIndex] = $personNameNarrativeRules;
            }
        }

        return $rules;
    }

    /**
     * Returns rules for job title.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getWarningForJobTitle($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $jobTitleIndex => $jobTitle) {
            $jobTitleForm = sprintf('%s.job_title.%s', $formBase, $jobTitleIndex);

            foreach (
                $this->getWarningForNarrative(
                    $jobTitle['narrative'],
                    $jobTitleForm
                ) as $jobTitleNarrativeIndex => $jobTitleNarrativeRules
            ) {
                $rules[$jobTitleNarrativeIndex] = $jobTitleNarrativeRules;
            }
        }

        return $rules;
    }

    /**
     * Returns rules for mailing address.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getWarningForMailingAddress($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $mailingAddressIndex => $mailingAddress) {
            $mailingAddressForm = sprintf('%s.mailing_address.%s', $formBase, $mailingAddressIndex);

            foreach (
                $this->getWarningForNarrative(
                    $mailingAddress['narrative'],
                    $mailingAddressForm
                ) as $mailingAddressNarrativeIndex => $mailingAddressNarrativeRules
            ) {
                $rules[$mailingAddressNarrativeIndex] = $mailingAddressNarrativeRules;
            }
        }

        return $rules;
    }

    /**
     * Returns rules for department.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getErrorsForDepartment($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $departmentIndex => $department) {
            $departmentForm = sprintf('%s.department.%s', $formBase, $departmentIndex);

            foreach (
                $this->getErrorsForNarrative(
                    $department['narrative'],
                    $departmentForm
                ) as $departmentNarrativeIndex => $departmentNarrativeRules
            ) {
                $rules[$departmentNarrativeIndex] = $departmentNarrativeRules;
            }
        }

        return $rules;
    }

    /**
     * Returns rules for organisation.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getErrorsForOrganisation($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $organisationIndex => $organisation) {
            $organisationForm = sprintf('%s.organisation.%s', $formBase, $organisationIndex);

            foreach (
                $this->getErrorsForNarrative(
                    $organisation['narrative'],
                    $organisationForm
                ) as $organisationNarrativeIndex => $organisationNarrativeRules
            ) {
                $rules[$organisationNarrativeIndex] = $organisationNarrativeRules;
            }
        }

        return $rules;
    }

    /**
     * Returns rules for person name.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getErrorsForPersonName($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $personNameIndex => $personName) {
            $personNameForm = sprintf('%s.person_name.%s', $formBase, $personNameIndex);

            foreach (
                $this->getErrorsForNarrative(
                    $personName['narrative'],
                    $personNameForm
                ) as $personNameNarrativeIndex => $personNameNarrativeRules
            ) {
                $rules[$personNameNarrativeIndex] = $personNameNarrativeRules;
            }
        }

        return $rules;
    }

    /**
     * Returns rules for job title.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getErrorsForJobTitle($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $jobTitleIndex => $jobTitle) {
            $jobTitleForm = sprintf('%s.job_title.%s', $formBase, $jobTitleIndex);

            foreach (
                $this->getErrorsForNarrative(
                    $jobTitle['narrative'],
                    $jobTitleForm
                ) as $jobTitleNarrativeIndex => $jobTitleNarrativeRules
            ) {
                $rules[$jobTitleNarrativeIndex] = $jobTitleNarrativeRules;
            }
        }

        return $rules;
    }

    /**
     * Returns rules for mailing address.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getErrorsForMailingAddress($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $mailingAddressIndex => $mailingAddress) {
            $mailingAddressForm = sprintf('%s.mailing_address.%s', $formBase, $mailingAddressIndex);

            foreach (
                $this->getErrorsForNarrative(
                    $mailingAddress['narrative'],
                    $mailingAddressForm
                ) as $mailingAddressNarrativeIndex => $mailingAddressNarrativeRules
            ) {
                $rules[$mailingAddressNarrativeIndex] = $mailingAddressNarrativeRules;
            }
        }

        return $rules;
    }

    /**
     * Returns rules for telephone.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getErrorsForTelephone($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $telephoneIndex => $telephone) {
            $rules[sprintf('%s.telephone.%s.telephone', $formBase, $telephoneIndex)] = ['nullable', 'min:7', 'max:20', new ValidPhoneNumber];
        }

        return $rules;
    }

    /**
     * Returns rules for email.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getErrorsForEmail($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $emailIndex => $email) {
            $rules[sprintf('%s.email.%s.email', $formBase, $emailIndex)] = [
                'nullable',
                ' email',
                'regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,}$/ix',
            ];
        }

        return $rules;
    }

    /**
     * rule for website.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getErrorsForWebsite($formFields, $formBase): array
    {
        $rules = [];

        foreach ($formFields as $websiteIndex => $website) {
            $rules[sprintf('%s.website.%s.website', $formBase, $websiteIndex)] = 'nullable|url';
        }

        return $rules;
    }

    /**
     * Get validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return $this->getMessagesForContactInfo($this->get('contact_info'));
    }

    /**
     * Returns messages for contact info.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getMessagesForContactInfo(array $formFields): array
    {
        $messages = [];

        foreach ($formFields as $contactInfoIndex => $contactInfo) {
            $contactInfoForm = sprintf('contact_info.%s', $contactInfoIndex);
            $messages[sprintf('%s.type.in', $contactInfoForm)] = trans('validation.type_is_invalid');
            $tempMessages = [
                $this->getMessagesForDepartment(Arr::get($contactInfo, 'department', []), $contactInfoForm),
                $this->getMessagesForOrganisation(Arr::get($contactInfo, 'organisation', []), $contactInfoForm),
                $this->getMessagesForPersonName(Arr::get($contactInfo, 'person_name', []), $contactInfoForm),
                $this->getMessagesForJobTitle(Arr::get($contactInfo, 'job_title', []), $contactInfoForm),
                $this->getMessagesForMailingAddress(
                    Arr::get($contactInfo, 'mailing_address', []),
                    $contactInfoForm
                ),
                $this->getMessagesForTelephone(Arr::get($contactInfo, 'telephone', []), $contactInfoForm),
                $this->getMessagesForEmail(Arr::get($contactInfo, 'email', []), $contactInfoForm),
                $this->getMessagesForWebsite(Arr::get($contactInfo, 'website', []), $contactInfoForm),
            ];

            foreach ($tempMessages as $tempMessage) {
                foreach ($tempMessage as $idx => $message) {
                    $messages[$idx] = $message;
                }
            }
        }

        return $messages;
    }

    /**
     * Returns messagess for department.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getMessagesForDepartment($formFields, $formBase): array
    {
        $messages = [];

        foreach ($formFields as $departmentIndex => $department) {
            $departmentForm = sprintf('%s.department.%s', $formBase, $departmentIndex);

            foreach (
                $this->getMessagesForNarrative(
                    $department['narrative'],
                    $departmentForm
                ) as $departmentNarrativeIndex => $departmentNarrativeMessages
            ) {
                $messages[$departmentNarrativeIndex] = $departmentNarrativeMessages;
            }
        }

        return $messages;
    }

    /**
     * Returns messages for organisation.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getMessagesForOrganisation($formFields, $formBase): array
    {
        $messages = [];

        foreach ($formFields as $organisationIndex => $organisation) {
            $organisationForm = sprintf('%s.organisation.%s', $formBase, $organisationIndex);

            foreach (
                $this->getMessagesForNarrative(
                    $organisation['narrative'],
                    $organisationForm
                ) as $organisationNarrativeIndex => $organisationNarrativeMessages
            ) {
                $messages[$organisationNarrativeIndex] = $organisationNarrativeMessages;
            }
        }

        return $messages;
    }

    /**
     * Returns messaged for person name.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getMessagesForPersonName($formFields, $formBase): array
    {
        $messages = [];

        foreach ($formFields as $personNameIndex => $personName) {
            $personNameForm = sprintf('%s.person_name.%s', $formBase, $personNameIndex);

            foreach (
                $this->getMessagesForNarrative(
                    $personName['narrative'],
                    $personNameForm
                ) as $personNameNarrativeIndex => $personNameNarrativeMessages
            ) {
                $messages[$personNameNarrativeIndex] = $personNameNarrativeMessages;
            }
        }

        return $messages;
    }

    /**
     * Returns messages for job title.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getMessagesForJobTitle($formFields, $formBase): array
    {
        $messages = [];

        foreach ($formFields as $jobTitleIndex => $jobTitle) {
            $jobTitleForm = sprintf('%s.job_title.%s', $formBase, $jobTitleIndex);

            foreach (
                $this->getMessagesForNarrative(
                    $jobTitle['narrative'],
                    $jobTitleForm
                ) as $jobTitleNarrativeIndex => $jobTitleNarrativeMessages
            ) {
                $messages[$jobTitleNarrativeIndex] = $jobTitleNarrativeMessages;
            }
        }

        return $messages;
    }

    /**
     * Returns messages for mailing address.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getMessagesForMailingAddress($formFields, $formBase): array
    {
        $messages = [];

        foreach ($formFields as $mailingAddressIndex => $mailingAddress) {
            $mailingAddressForm = sprintf('%s.mailing_address.%s', $formBase, $mailingAddressIndex);

            foreach (
                $this->getMessagesForNarrative(
                    $mailingAddress['narrative'],
                    $mailingAddressForm
                ) as $mailingAddressNarrativeIndex => $mailingAddressNarrativeMessages
            ) {
                $messages[$mailingAddressNarrativeIndex] = $mailingAddressNarrativeMessages;
            }
        }

        return $messages;
    }

    /**
     * Returns messages for telephone.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     **/
    protected function getMessagesForTelephone($formFields, $formBase): array
    {
        $messages = [];

        foreach ($formFields as $telephoneIndex => $telephone) {
            $messages[sprintf(
                '%s.telephone.%s.telephone.numeric',
                $formBase,
                $telephoneIndex
            )]
                = trans('validation.activity_contact_info.telephone.numeric');
            $messages[sprintf(
                '%s.telephone.%s.telephone.regex',
                $formBase,
                $telephoneIndex
            )]
                = trans('validation.activity_contact_info.telephone.regex');
            $messages[sprintf(
                '%s.telephone.%s.telephone.min',
                $formBase,
                $telephoneIndex
            )]
                = trans('validation.activity_contact_info.telephone.min');
            $messages[sprintf(
                '%s.telephone.%s.telephone.max',
                $formBase,
                $telephoneIndex
            )]
                = trans('validation.activity_contact_info.telephone.max');
        }

        return $messages;
    }

    /**
     * Returns messages for email.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getMessagesForEmail($formFields, $formBase): array
    {
        $messages = [];

        foreach ($formFields as $emailIndex => $email) {
            $messages[sprintf(
                '%s.email.%s.email.email',
                $formBase,
                $emailIndex
            )]
                = trans('validation.email_address_format_is_invalid');
            $messages[sprintf(
                '%s.email.%s.email.regex',
                $formBase,
                $emailIndex
            )]
                = trans('validation.email_address_format_is_invalid');
        }

        return $messages;
    }

    /**
     * returns messages for website.
     *
     * @param $formFields
     * @param $formBase
     *
     * @return array
     */
    protected function getMessagesForWebsite($formFields, $formBase): array
    {
        $messages = [];

        foreach ($formFields as $websiteIndex => $website) {
            $messages[sprintf(
                '%s.website.%s.website.url',
                $formBase,
                $websiteIndex
            )]
                = trans('validation.url_valid');
        }

        return $messages;
    }
}
