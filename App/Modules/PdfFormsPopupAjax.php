<?php

namespace PdfFormsLoader\Modules;

use PdfFormsLoader\Core\CustomRequest;
use PdfFormsLoader\Core\Ui\FieldsMapper;
use PdfFormsLoader\Services\PdfFillerApiService;
use PdfFormsLoader\Services\PdfFillerManager\PdfFillerTemplatesManager;

class PdfFormsPopupAjax
{
    const MAX_TEMPLATES_FOR_USER = 1000;

    private $templateIdIsNotSetError;

    public function __construct()
    {
        $this->setErrorMessages();
    }

    public function bindTemplateAndGetFillableFields()
    {
        $params = CustomRequest::instance()->postOrGet();
        $templateId = (int)$params->get('templateId');
        $postId = (int)$params->get('postId');

        if (!$templateId || !$postId) {
            wp_die(json_encode(['error' => $this->templateIdIsNotSetError]));
        }

        $pdffillerApiService = PDFFillerApiService::getInstance();
        $fields = $pdffillerApiService->getFieldsWithCaching($templateId);
        $fields = $this->mapFields($fields);

        $this->bindTemplateToPost($templateId, $postId);

        wp_die(json_encode($fields));
    }

    /**
     * @throws \PDFfiller\OAuth2\Client\Provider\Exceptions\InvalidQueryException
     * @throws \PDFfiller\OAuth2\Client\Provider\Exceptions\InvalidRequestException
     * @throws \ReflectionException
     */
    public function freshTemplates()
    {
        $this->resetOption('pdfform_fillable_templates');

        $formList = PdfFillerApiService::getInstance()->getTemplatesWithCaching();

        if ($this->currentBindTemplateIsInTheList($formList)) {
            wp_die(json_encode($formList));
        }

        try{
            $bindedTemplate = $this->getBindTemplate();
        } catch (\Exception $exception) {
            wp_die(json_encode($formList));
        }

        $formList[$bindedTemplate->id] = $bindedTemplate->name.' '.date("Y-m-d H:i", $bindedTemplate->created);

        wp_die(json_encode($formList));
    }

    public function allTemplates()
    {
        $page = 1;
        $fullList = [];

        while (count($fullList) < self::MAX_TEMPLATES_FOR_USER) {
            $this->resetOption('pdfform_fillable_templates');
            $newTemplateList = PdfFillerApiService::getInstance()->getTemplatesWithCaching($page);
            $page++;

            $fullList += $newTemplateList;

            if ($this->isThisIsLastTemplatesPage($newTemplateList)) {
                break;
            }
        }

        wp_die(json_encode($fullList));
    }

    private function mapFields(array $fields): array
    {
        $fieldsMapper = new FieldsMapper();

        $newFields = [];
        foreach ($fields as $field) {
            $newFields[$field->name] = (object)[
                'fieldAttr' => $fieldsMapper->prepareShortCodeAttr($field),
                'text' => $field->name,
                'type' => 'button',
            ];
        }

        return $newFields;
    }

    private function currentBindTemplateIsInTheList(array $formList): bool
    {
        $currentTemplateId = $this->getCurrentTemplateId();

        if (is_int($currentTemplateId)) {
            return array_has($formList, $currentTemplateId);
        }

        return false;
    }

    /**
     * @return \stdClass
     * @throws \PDFfiller\OAuth2\Client\Provider\Exceptions\InvalidQueryException
     * @throws \PDFfiller\OAuth2\Client\Provider\Exceptions\InvalidRequestException
     * @throws \ReflectionException
     * @throws \Exception
     */
    private function getBindTemplate(): \stdClass
    {
        $currentTemplateId = $this->getCurrentTemplateId();

        if (!is_int($currentTemplateId)) {
            throw new \Exception($this->templateIdIsNotSetError);
        }

        return PdfFillerApiService::getInstance()->getTemplate($currentTemplateId);
    }

    private function getCurrentTemplateId()
    {
        $params = CustomRequest::instance()->postOrGet();
        $currentPostId = $params->get('postId');

        if (empty($currentPostId)) {
            return false;
        }

        $postMetaTemplateIdArray = get_post_meta($currentPostId, 'fillable_template_list_fillable_template_list');

        if (empty($postMetaTemplateIdArray)) {
            return false;
        }

        return (int)reset($postMetaTemplateIdArray);
    }

    private function resetOption(string $option)
    {
        update_option($option, ['expires' => 0, 'items' => '']);
    }

    private function isThisIsLastTemplatesPage(array $templateList)
    {
        return count($templateList) < PdfFillerTemplatesManager::TEMPLATES_PER_PAGE;
    }

    private function bindTemplateToPost(int $templateId, int $postId)
    {
        update_post_meta($postId, 'fillable_template_list_fillable_template_list', $templateId);
    }

    private function setErrorMessages()
    {
        $this->templateIdIsNotSetError = __('templateID is not set');
    }
}
