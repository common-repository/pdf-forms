<?php

namespace PdfFormsLoader\Services;

use GuzzleHttp\Client;
use League\OAuth2\Client\Token\AccessToken;
use PDFfiller\OAuth2\Client\Provider\{
    Exceptions\InvalidRequestException,
    Exceptions\OptionsMissingException,
    Exceptions\ResponseException,
    PDFfiller,
    Template};
use PdfFormsLoader\Models\{MainSettingsModel, PDFFillerModel};
use PdfFormsLoader\Services\PdfFillerManager\{PdfFillerFieldsManager,
    PdfFillerFormManager,
    PdfFillerTemplatesManager};
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PdfFillerApiService
 * @package PdfFormsLoader\Services
 * @property PDFfiller $PDFFillerProvider
 */
class PdfFillerApiService
{
    private $PDFFillerProvider;
    private $tokenValid = false;
    static private $instance = null;

    private function __construct()
    {
        $this->initPdfFillerProvider();
        $this->initOauthToken();
    }

    private function __clone()
    {
        // NOOP
    }

    private function __wakeup()
    {
        // NOOP
    }

    private function __sleep()
    {
        // NOOP
    }

    static public function getInstance()
    {
        return
            self::$instance === null
                ? self::$instance = new static()
                : self::$instance;
    }

    /**
     * @param int $templateId
     * @return \stdClass
     * @throws \PDFfiller\OAuth2\Client\Provider\Exceptions\InvalidQueryException
     * @throws \PDFfiller\OAuth2\Client\Provider\Exceptions\InvalidRequestException
     * @throws \ReflectionException
     */
    public function getTemplate(int $templateId): \stdClass
    {
        $template = get_option('pdfform_document_'.$templateId, []);

        if ($this->isOptionNotExpired($template)) {
            return $template['data'];
        }

        $pdfFillerTemplateModel = new PdfFillerTemplatesManager($this->PDFFillerProvider);
        $template = $pdfFillerTemplateModel->one($templateId);
        $template = $pdfFillerTemplateModel->formatTemplateData($template);

        update_option('pdfform_document_'.$templateId,
            ['expires' => time() + PDFFillerModel::EXPIRES_DOCUMENT, 'data' => $template]);

        return $template;
    }

    /**
     * @param int $templateListPage
     * @return array
     */
    public function getTemplatesWithCaching(int $templateListPage = 1): array
    {
        $fillableTemplates = get_option('pdfform_fillable_templates', []);

        if ($this->isOptionNotExpired($fillableTemplates)) {
            return $fillableTemplates['items'];
        }

        $pdfFillerTemplateModel = new PdfFillerTemplatesManager($this->PDFFillerProvider);
        $templates = $pdfFillerTemplateModel->allTemplates($templateListPage);

        $templates = $pdfFillerTemplateModel->formatTemplateList($templates);

        $expiredTime = time() + PDFFillerModel::EXPIRES;
        update_option('pdfform_fillable_templates', ['expires' => $expiredTime, 'items' => $templates]);

        return $templates;
    }

    public function getFieldsWithCaching(int $templateId): array
    {
        $fillableFields = get_option('pdfform_fillable_fields_'.$templateId, []);

        if ($this->isOptionNotExpired($fillableFields)) {
            return $fillableFields['items'];
        }

        $pdfFillerFillableFieldsService = new PdfFillerFieldsManager($this->PDFFillerProvider);
        $fields = $pdfFillerFillableFieldsService->allFields($templateId);
        $fields = $pdfFillerFillableFieldsService->formatFieldList($fields);

        $expiredTime = time() + PDFFillerModel::EXPIRES;
        update_option('pdfform_fillable_fields_'.$templateId,
            ['expires' => $expiredTime, 'items' => $fields]);

        return $fields;
    }

    /**
     * @return array
     */
    public function getLinkToFillDocuments(): array
    {
        $l2fList = get_option('pdfform_l2f_list', []);

        if ($this->isOptionNotExpired($l2fList)) {
            return $l2fList['items'];
        }

        $pdfFillerFillableFieldsService = new PdfFillerFormManager($this->PDFFillerProvider);
        $l2fForms = $pdfFillerFillableFieldsService->allForms();
        $l2fForms = $pdfFillerFillableFieldsService->formatFormList($l2fForms);

        update_option('pdfform_l2f_list', ['expires' => time() + PDFFillerModel::EXPIRES, 'items' => $l2fForms]);

        return $l2fForms;
    }

    /**
     * @param int $documentId
     * @return mixed
     * @throws \PDFfiller\OAuth2\Client\Provider\Exceptions\InvalidQueryException
     * @throws \PDFfiller\OAuth2\Client\Provider\Exceptions\InvalidRequestException
     */
    public function getTemplateContent(int $documentId)
    {
        return Template::download($this->PDFFillerProvider, $documentId);
    }


    /**
     * @param int $fillableTemplateId
     * @param array $fields
     * @return int
     * @throws InvalidRequestException
     * @throws \PDFfiller\OAuth2\Client\Provider\Exceptions\InvalidQueryException
     * @throws \ReflectionException
     */
    public function saveTemplate(int $fillableTemplateId, array $fields): int
    {
        $fillableTemplate = new Template($this->PDFFillerProvider);
        $fillableTemplate->id = $fillableTemplateId;

        $resultFields = $fillableTemplate->fill($fields);

        return array_get($resultFields, 'id');
    }

    /**
     * @param int $templateId
     * @throws InvalidRequestException
     * @throws ResponseException
     * @throws \PDFfiller\OAuth2\Client\Provider\Exceptions\InvalidQueryException
     * @throws \ReflectionException
     */
    public function addToTemplateNameCurrentDate(int $templateId)
    {
        $template = Template::one($this->PDFFillerProvider, $templateId);

        $newTemplateName = $this->formNewTemplateName($template);

        $template->name = $newTemplateName;
        $template->save();
    }

    /**
     * @return bool
     */
    public function isTokenValid(): bool
    {
        return $this->tokenValid;
    }

    private function initOauthToken()
    {
        $pdffillerOauthToken = MainSettingsModel::getSettingItemCache('pdffiller-oauth-token');

        if (empty($pdffillerOauthToken)) {
            return;
        }

        $this->PDFFillerProvider->setAccessToken(new AccessToken(['access_token' => $pdffillerOauthToken]));

        if ($this->checkToken()) {
            $this->tokenValid = true;
        }
    }

    private function initPdfFillerProvider()
    {
        $options = [
            'urlAccessToken' => PDFFillerModel::PDFFILLER_API_DOMAIN.'/v2/oauth/token',
            'urlApiDomain' => PDFFillerModel::PDFFILLER_API_DOMAIN.'/v2/',
        ];

        $collaborators = [
            'httpClient' => new Client([
                'headers' => [
                    'integration-name' => 'wordpress',
                    'domain-home-url' => get_home_url(),
                ],
            ]),
        ];

        try {
            $this->PDFFillerProvider = new PDFfiller($options, $collaborators);
        } catch (OptionsMissingException $e) {
            // NOOP
        }
    }

    private function checkToken(): bool
    {
        try {
            $this->PDFFillerProvider->apiCall('GET', PDFFillerModel::PDFFILLER_API_DOMAIN.'/v2/users/me');
        } catch (\Exception $exception) {
            return false;
        }

        return $this->PDFFillerProvider->getStatusCode() == Response::HTTP_OK;
    }

    private function isOptionNotExpired(array $option): bool
    {
        return !empty($option['expires']) && $option['expires'] >= time();
    }

    /**
     * @param Template $template
     * @return string
     */
    private function formNewTemplateName(Template $template): string
    {
        $templateNameTransformedToArray = (explode('.', $template->name));
        array_pop($templateNameTransformedToArray);
        $newTemplateName = implode('.', $templateNameTransformedToArray).'_'.date('Y-m-d-H-i');

        return $newTemplateName;
    }
}
