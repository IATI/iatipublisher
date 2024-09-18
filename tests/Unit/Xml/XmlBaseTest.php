<?php

declare(strict_types=1);

namespace Tests\Unit\Xml;

use App\XmlImporter\Foundation\Mapper\Components\XmlMapper;
use App\XmlImporter\Foundation\Support\Factory\Validation;
use App\XmlImporter\Foundation\Support\Factory\XmlValidator;
use App\XmlImporter\Foundation\Support\Providers\TemplateServiceProvider;
use App\XmlImporter\Foundation\Support\Providers\XmlServiceProvider;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Tests\Unit\ImportBaseTest;

/**
 * Class XmlBaseTest.
 */
class XmlBaseTest extends ImportBaseTest
{
    /**
     * @var string
     */
    private string $completeXmlFile = 'tests/Unit/TestFiles/Xml/complete.xml';

    /**
     * @var object
     */
    protected object $validation;

    /**
     * @var array
     */
    protected array $completeXml;

    /**
     * @return void
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->signIn();
        $this->getXmlActivity();
        $this->validation = app()->make(Validation::class);
    }

    /**
     * Sets complete xml data.
     *
     * @return void
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getXmlActivity(): void
    {
        $this->completeXml = $this->getMappedActivity();
    }

    /**
     * Gets mapped activity in an array.
     *
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getMappedActivity(): array
    {
        $xmlData = $this->loadXmlData();
        $template = app()->make(TemplateServiceProvider::class)->load();
        $orgRef = $this->organization->identifier;
        $reportingOrg = $this->user->organization->reporting_org;
        $mapper = new XmlMapper();

        return $mapper->mapForTest($xmlData, $template, $orgRef, $reportingOrg);
    }

    /**
     * Load complete xml file  and reads.
     *
     * @return array
     */
    public function loadXmlData(): array
    {
        $contents = file_get_contents($this->completeXmlFile);
        $xmlServiceProvider = app()->make(XmlServiceProvider::class);

        return $xmlServiceProvider->load($contents);
    }

    /**
     * Collects validation messages.
     *
     * @param $rows
     *
     * @return array
     * @throws BindingResolutionException
     * @throws \JsonException
     */
    public function getErrors($rows): array
    {
        $errors = [];
        $xmlValidator = new XmlValidator($this->validation);

        foreach ($rows as $row) {
            $errors[] = $xmlValidator->init($row)->validateActivity(false, true);
        }

        return Arr::flatten($errors);
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->completeXml, $this->validation, $this->user, $this->organization);
    }
}
