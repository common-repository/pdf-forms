<?php
namespace PdfFormsLoader\Services\PdfFillerManager;

use PDFfiller\OAuth2\Client\Provider\Template;
use PdfFormsLoader\Models\PDFFillerModel;

class PdfFillerFieldsManager extends PdfFillerManager
{
    public function allFields(int $templateId): array
    {
        try {
            $template = new Template($this->getPDFFillerProvider(), ['id' => $templateId]);

            return $template->fields();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function formatFieldList(array $fields):array
    {
        $fields = $this->filterByAllowedTextTypes($fields);
        return $this->transformSubArraysToObjects($fields);
    }

    private function filterByAllowedTextTypes(array $fields): array
    {
        $fields = array_filter($fields, function ($field) {
            if (empty($field['type'])) {
                return false;
            }

            return in_array($field['type'], PDFFillerModel::ALLOWED_FIELD_TYPE);
        });

        return $fields;
    }

    private function transformSubArraysToObjects(array $fields): array
    {
        return array_map(function ($field) {
            return (object)$field;
        }, $fields);
    }
}
