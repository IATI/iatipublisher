<?php

declare(strict_types=1);

namespace App\IATI\Services\Organization;

use App\IATI\Elements\Builder\ParentCollectionFormCreator;
use App\IATI\Models\Organization\Organization;
use App\IATI\Repositories\Document\DocumentRepository;
use App\IATI\Repositories\Organization\OrganizationRepository;
use App\IATI\Traits\DataSanitizeTrait;
use App\IATI\Traits\OrganizationXmlBaseElements;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Kris\LaravelFormBuilder\Form;

/**
 * Class DocumentLinkService.
 */
class DocumentLinkService
{
    use DataSanitizeTrait;
    use OrganizationXmlBaseElements;

    /**
     * @var ParentCollectionFormCreator
     */
    protected ParentCollectionFormCreator $parentCollectionFormCreator;

    /**
     * @var OrganizationRepository
     */
    protected OrganizationRepository $organizationRepository;

    protected DocumentRepository $documentRepository;

    /**
     * DocumentLinkService constructor.
     *
     * @param OrganizationRepository $organizationRepository
     * @param ParentCollectionFormCreator $parentCollectionFormCreator
     */
    public function __construct(OrganizationRepository $organizationRepository, DocumentRepository $documentRepository, ParentCollectionFormCreator $parentCollectionFormCreator)
    {
        $this->organizationRepository = $organizationRepository;
        $this->documentRepository = $documentRepository;
        $this->parentCollectionFormCreator = $parentCollectionFormCreator;
    }

    /**
     * Returns document link of an organization.
     *
     * @param int $organization_id
     *
     * @return array|null
     */
    public function getDocumentLinkData(int $organization_id): ?array
    {
        return $this->organizationRepository->find($organization_id)->document_link;
    }

    /**
     * Returns Organization object.
     *
     * @param $id
     *
     * @return Model
     */
    public function getOrganizationData($id): Organization
    {
        return $this->organizationRepository->getOrganizationData($id);
    }

    /**
     * Updates Organization document link.
     *
     * @param $id
     * @param $documentLink
     *
     * @return bool
     */
    public function update($id, $documentLinks): bool
    {
        $organizationId = Auth::user()->organization->id;

        foreach ($documentLinks['document_link'] as $index => $documentLink) {
            $document = Arr::get($documentLink, 'document', null);

            if ($document) {
                $extension = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
                $fileName = basename($document->getClientOriginalName(), ".$extension") . time() . '.' . $extension;

                $documentData['activity_id'] = null;
                $documentData['activities'] = null;
                $documentData['organization_id'] = $organizationId;
                $documentData['filename'] = $fileName;
                $documentData['extension'] = $extension;
                $documentData['document_link'] = $documentLink;

                awsUploadFile("/document-link/$organizationId/" . $fileName, $document->get());
                $this->documentRepository->store($documentData);

                $documentLink['url'] = awsUrl("/document-link/$organizationId/" . $fileName);
            }

            unset($documentLink['document']);
            $documentLinks['document_link'][$index] = $documentLink;
        }

        $documentLinks = $this->sanitizeData($documentLinks['document_link']);

        $organisation = $this->organizationRepository->find($id);
        $deprecationStatusMap = $organisation->deprecation_status_map;
        $deprecationStatusMap['document_link'] = doesOrganisationDocumentLinkHaveDeprecatedCode($documentLinks);

        return  $this->organizationRepository->update($id, [
            'document_link'          => $documentLinks,
            'deprecation_status_map' => $deprecationStatusMap,
        ]);
    }

    /**
     * Forms document link form.
     *
     * @param $id
     *
     * @return Form
     */
    public function formGenerator($id, $deprecationStatusMap = []): Form
    {
        $element = readOrganizationElementJsonSchema();
        $model['document_link'] = $this->getDocumentLinkData($id) ?? [];

        $this->parentCollectionFormCreator->url = route('admin.organisation.document-link.update', [$id]);

        return $this->parentCollectionFormCreator->editForm($model, $element['document_link'], 'PUT', '/organisation');
    }

    /**
     * Returns data in required xml array format.
     *
     * @param Organization $organization
     *
     * @return array
     */
    public function getXmlData(Organization $organization): array
    {
        $organizationData = [];
        $documentLinks = (array) $organization->document_link;

        if (count($documentLinks)) {
            foreach ($documentLinks as $documentLink) {
                $categories = [];

                foreach (Arr::get($documentLink, 'category', []) as $value) {
                    $categories[] = [
                        '@attributes' => ['code' => Arr::get($value, 'code', null)],
                    ];
                }

                $languages = [];

                foreach (Arr::get($documentLink, 'language', []) as $language) {
                    $languages[] = [
                        '@attributes' => ['code' => Arr::get($language, 'language', null)],
                    ];
                }

                $recipientCountries = [];

                foreach (Arr::get($documentLink, 'recipient_country', []) as $recipient_country) {
                    $recipientCountries[] = [
                        '@attributes' => [
                            'code'       => Arr::get($recipient_country, 'code', null),
                            'percentage' => Arr::get($recipient_country, 'percentage', null),
                        ],
                        'narrative'   => $this->buildNarrative(Arr::get($recipient_country, 'narrative', null)),
                    ];
                }

                $organizationData[] = [
                    '@attributes'   => [
                        'url'    => Arr::get($documentLink, 'url', null),
                        'format' => Arr::get($documentLink, 'format', null),
                    ],
                    'title'         => [
                        'narrative' => $this->buildNarrative(Arr::get($documentLink, 'title.0.narrative', [])),
                    ],
                    'description'   => [
                        'narrative' => $this->buildNarrative(Arr::get($documentLink, 'description.0.narrative', [])),
                    ],
                    'category'      => $categories,

                    'language'      => $languages,
                    'document-date' => [
                        '@attributes' => [
                            'iso-date' => Arr::get($documentLink, 'document_date.0.date', null),
                        ],
                    ],
                    'recipient-country' => $recipientCountries,
                ];
            }
        }

        return $organizationData;
    }
}
