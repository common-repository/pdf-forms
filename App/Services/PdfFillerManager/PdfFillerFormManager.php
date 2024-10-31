<?php
namespace PdfFormsLoader\Services\PdfFillerManager;

use PDFfiller\OAuth2\Client\Provider\FillableForm;

class PdfFillerFormManager extends PdfFillerManager
{
    const L2F_FORMS_PER_PDFFILLER_REQUEST = 100;

    public function allForms():array
    {
        $options = ['perpage' => self::L2F_FORMS_PER_PDFFILLER_REQUEST];
        try {
            $response = FillableForm::all($this->getPDFFillerProvider(), $options);
        } catch (\Exception $e) {
            return [];
        }

        return $response->getList();
    }

    public function formatFormList(array $l2fForms): array
    {
        $newL2FList = [];

        foreach ($l2fForms as $id => $form) {
            $newL2FList[] = [
                'document_id' => $form->document_id,
                'name' => $form->document_name,
                'url' => $form->url,
            ];
        }

        return $newL2FList;
    }
}
