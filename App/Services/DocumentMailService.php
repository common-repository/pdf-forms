<?php
namespace PdfFormsLoader\Services;

use PdfFormsLoader\Facades\MailFacade;

class DocumentMailService extends MailFacade
{
    const DEFAULT_SUBJECT = 'PDFForm';
    const DEFAULT_MESSAGE = 'You can download filled form.';

    public function sendDocument($fillableTemplateId, $fields, $emails, $subject = null, $message = null) {
        try {
            $pdffiller = PdfFillerApiService::getInstance();
            $templateId = $pdffiller->saveTemplate($fillableTemplateId, $fields);
            $pdffiller->addToTemplateNameCurrentDate($templateId);

            $filesService = new FilesService();
            $filesService->setFileFromPDFFiller($templateId)->removeAfterLoadSite();

            if (empty($emails)) {
                return true;
            }

            $subject = !empty($subject) ? $subject : self::DEFAULT_SUBJECT;
            $message = !empty($message) ? $message : self::DEFAULT_MESSAGE;

            $this->setParams([
                'to' => $emails,
                'subject' => $subject,
                'message' => $message,
                'headers' => [],
                'attachments' => [$filesService->getFullPath()],
            ]);

            $result = $this->send();

            return $result;
        } catch (\Exception $e) {
            //echo $e->getMessage();
            return false;
        }
    }
}
