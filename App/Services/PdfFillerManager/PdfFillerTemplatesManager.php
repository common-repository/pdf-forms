<?php
namespace PdfFormsLoader\Services\PdfFillerManager;

use PDFfiller\OAuth2\Client\Provider\Template;

class PdfFillerTemplatesManager extends PdfFillerManager
{
    const TEMPLATES_PER_PAGE = 100;


    public function allTemplates(int $templateListPage): array
    {
        try {
            $response = Template::all($this->getPDFFillerProvider(),
                ['per_page' => self::TEMPLATES_PER_PAGE, 'page' => $templateListPage]);
        } catch (\Exception $e) {
            return [];
        }

        return $response->getList();
    }

    public function formatTemplateList(array $templateList): array
    {
        $documents = [];
        foreach ($templateList as $template) {
            $documents[$template->id] = $template->name.' '.date("Y-m-d H:i", $template->created);
        }

        return $documents;
    }

    /**
     * @param int $templateId
     * @return Template
     * @throws \PDFfiller\OAuth2\Client\Provider\Exceptions\InvalidQueryException
     * @throws \PDFfiller\OAuth2\Client\Provider\Exceptions\InvalidRequestException
     * @throws \ReflectionException
     */
    public function one(int $templateId): Template
    {
        return Template::one($this->getPDFFillerProvider(), $templateId);
    }

    public function formatTemplateData(Template $template): \stdClass
    {
        $newTemplateObject = new \stdClass;
        $newTemplateObject->id = $template->id;
        $newTemplateObject->name = $template->name;
        $newTemplateObject->created = $template->created;

        return $newTemplateObject;
    }
}
