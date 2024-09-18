<?php

declare(strict_types=1);

namespace App\Http\Requests\Organization\DocumentLink;

use App\Http\Requests\Organization\OrganizationBaseRequest;

/**
 * Class DocumentLinkRequest.
 */
class DocumentLinkRequest extends OrganizationBaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Rules for document link.
     *
     * @return array
     */
    public function rules(): array
    {
        return $this->getWarningForDocumentLink($this->get('document_link'));
    }

    /**
     * Custom message for rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return $this->getMessagesForDocumentLink($this->get('document_link'));
    }

    /**
     * Rules for fields of document link form.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getWarningForDocumentLink(array $formFields): array
    {
        $rules = [];

        foreach ($formFields as $documentLinkIndex => $documentLink) {
            $documentLinkForm = sprintf(
                'document_link.%s',
                $documentLinkIndex
            );
            $rules[sprintf('document_link.%s.url', $documentLinkIndex)] = 'nullable|url';
            $rules[sprintf('document_link.%s.format', $documentLinkIndex)] = sprintf(
                'nullable|in:%s',
                implode(
                    ',',
                    array_keys(
                        $this->getCodeListForRequestFiles('FileFormat', 'Activity')
                    )
                )
            );
            $rules[sprintf('document_link.%s.document_date.0.date', $documentLinkIndex)] = 'nullable|date';
            $rules = array_merge(
                $rules,
                $this->getWarningForNarrative(
                    $documentLink['title'][0]['narrative'],
                    sprintf('%s.title.0', $documentLinkForm),
                    true
                ),
                $this->getWarningForNarrative(
                    $documentLink['description'][0]['narrative'],
                    sprintf('%s.description.0', $documentLinkForm)
                ),
                $this->getWarningForDocumentCategory($documentLink['category'], $documentLinkForm),
                $this->getWarningForDocumentLanguage($documentLink['language'], $documentLinkForm),
                $this->getWarningForRecipientCountry($documentLink['recipient_country'], $documentLinkForm)
            );
        }

        return $rules;
    }

    /**
     * Custom message for document link.
     *
     * @param  array  $formFields
     *
     * @return array
     */
    public function getMessagesForDocumentLink(array $formFields): array
    {
        $messages = [];

        foreach ($formFields as $documentLinkIndex => $documentLink) {
            $documentLinkForm = sprintf(
                'document_link.%s',
                $documentLinkIndex
            );
            $messages[sprintf('document_link.%s.url.required', $documentLinkIndex)] = trans(
                'validation.required',
                ['attribute' => trans(' elements/label.url')]
            );
            $messages[sprintf('document_link.%s.url.url', $documentLinkIndex)]
                = trans('validation.url', ['attribute' => trans(' elements/label.url')]);
            $messages[sprintf('document_link.%s.format.required', $documentLinkIndex)] = trans(
                'validation.required',
                ['attribute' => trans(' elements/label.format')]
            );
            $messages[sprintf('document_link.%s.document_date.0.date.date', $documentLinkIndex)] = trans(
                'validation.date',
                ['attribute' => trans(' elements/label.date')]
            );
            $messages = array_merge(
                $messages,
                $this->getMessagesForNarrative(
                    $documentLink['title'][0]['narrative'],
                    sprintf('%s.title.0', $documentLinkForm)
                ),
                $this->getMessagesForNarrative(
                    $documentLink['description'][0]['narrative'],
                    sprintf('%s.description.0', $documentLinkForm)
                ),
                $this->getMessagesForDocumentCategory($documentLink['category'], $documentLinkForm),
                $this->getMessagesForDocumentLanguage($documentLink['language'], $documentLinkForm),
                $this->getMessagesForRecipientCountry($documentLink['recipient_country'], $documentLinkForm)
            );
        }

        return $messages;
    }

    /**
     * Rules for document link category field.
     *
     * @param $formFields
     * @param $formIndex
     *
     * @return array
     */
    public function getWarningForDocumentCategory($formFields, $formIndex): array
    {
        $rules = [];
        $rules[sprintf('%s.category', $formIndex)] = 'unique_category';

        foreach ($formFields as $documentCategoryIndex => $documentCategory) {
            $rules[sprintf('%s.category.%s.code', $formIndex, $documentCategoryIndex)] = sprintf(
                'nullable|in:%s',
                implode(
                    ',',
                    array_keys(
                        $this->getCodeListForRequestFiles('DocumentCategory', 'Organization')
                    )
                )
            );
        }

        return $rules;
    }

    /**
     * Custom message for document link category.
     *
     * @param $formFields
     * @param $formIndex
     *
     * @return array
     */
    public function getMessagesForDocumentCategory($formFields, $formIndex): array
    {
        $messages = [];
        $messages[sprintf(
            '%s.category.unique_category',
            $formIndex
        )]
            = trans('validation.organization_document_link.category_code.unique');

        foreach ($formFields as $documentCategoryIndex => $documentCategory) {
            $messages[sprintf(
                '%s.category.%s.code.required',
                $formIndex,
                $documentCategoryIndex
            )]
                = trans('validation.this_field_is_required');
        }

        return $messages;
    }

    /**
     * Rules for document language code field.
     *
     * @param $formFields
     * @param $formIndex
     *
     * @return array
     */
    public function getWarningForDocumentLanguage($formFields, $formIndex): array
    {
        $rules = [];
        $rules[sprintf('%s.language', $formIndex)] = 'unique_language';

        foreach ($formFields as $documentCategoryIndex => $documentCategory) {
            $rules[sprintf('%s.language.%s.language', $formIndex, $documentCategoryIndex)] = sprintf(
                'nullable|in:%s',
                implode(
                    ',',
                    array_keys(
                        $this->getCodeListForRequestFiles('Language', 'Activity')
                    )
                )
            );
        }

        return $rules;
    }

    /**
     * Custom message for document language.
     *
     * @param $formFields
     * @param $formIndex
     *
     * @return array
     */
    public function getMessagesForDocumentLanguage($formFields, $formIndex): array
    {
        $messages = [];
        $messages[sprintf(
            '%s.language.unique_language',
            $formIndex
        )]
            = trans('validation.organization_document_link.language.unique');

        foreach ($formFields as $documentCategoryIndex => $documentCategory) {
            $messages[sprintf(
                '%s.language.%s.code.required',
                $formIndex,
                $documentCategoryIndex
            )]
                = trans('validation.this_field_is_required');
        }

        return $messages;
    }

    /**
     * Rules for recipient country of document link.
     *
     * @param $formFields
     * @param $formIndex
     *
     * @return array
     */
    public function getWarningForRecipientCountry($formFields, $formIndex): array
    {
        $rules = [];

        foreach ($formFields as $recipientCountryIndex => $recipientCountryVal) {
            $budgetItemForm = sprintf('%s.recipient_country.%s', $formIndex, $recipientCountryIndex);
            $rules[sprintf('%s.code', $budgetItemForm)] = sprintf(
                'nullable|in:%s',
                implode(
                    ',',
                    array_keys(
                        $this->getCodeListForRequestFiles('Country', 'Activity')
                    )
                )
            );

            foreach (
                $this->getWarningForNarrative(
                    $recipientCountryVal['narrative'],
                    $budgetItemForm
                ) as $index => $rule
            ) {
                $rules[$index] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Custom rules for recipient country of document link.
     *
     * @param $formFields
     * @param $formIndex
     *
     * @return array
     */
    public function getMessagesForRecipientCountry($formFields, $formIndex): array
    {
        $messages = [];

        foreach ($formFields as $recipientCountryIndex => $recipientCountryVal) {
            $budgetItemForm = sprintf('%s.recipient_country.%s', $formIndex, $recipientCountryIndex);
            $messages = array_merge(
                $messages,
                $this->getMessagesForNarrative($recipientCountryVal['narrative'], $budgetItemForm)
            );
        }

        return $messages;
    }
}
