<?php
namespace PdfFormsLoader\Modules;

use PdfFormsLoader\Core\{CustomRequest, JsVariables};
use PdfFormsLoader\Facades\MetaBoxesFacade;
use PdfFormsLoader\Helpers\DescriptionMessageHelper;
use PdfFormsLoader\Models\PDFForm;
use PdfFormsLoader\Services\PdfFillerApiService;

class PdfFormMetaboxes
{
    const SERVER_VARIABLE_PHP_SELF = 'PHP_SELF';
    const SERVER_VARIABLE_PHP_SELF_VALUE = '/wp-admin/post-new.php';

    public function __construct()
    {
        if ($this->checkPostType()) {
            $this->addMetaboxes();
            JsVariables::addVariable('pdffiller_form_post', true);
            if (CustomRequest::instance()->postOrGet('post')) {
                JsVariables::addVariable('pdffiller_form_post_id', CustomRequest::instance()->postOrGet('post'));
            } else {
                add_action('save_post', [$this, 'savePostAction']);
            }
        }
    }

    public function savePostAction($argument)
    {
        JsVariables::addVariable('pdffiller_form_post_id', $argument);
    }

    private function addMetaboxes()
    {
        $documents = PDFFillerApiService::getInstance()->getTemplatesWithCaching();

        $messageHelper = new DescriptionMessageHelper();
        $defaultDocumentId = (int)get_post_meta(CustomRequest::instance()->get('post'), 'fillable_template_list_fillable_template_list', true);

        try {
            $defaultDocument = PDFFillerApiService::getInstance()->getTemplate($defaultDocumentId);
            $defaultDocument->name .= ' '.date("Y-m-d H:i", $defaultDocument->created);
        } catch (\Exception $e) {
            $defaultDocument = null;
        }

        MetaBoxesFacade::make([
            'slug' => 'fillable_template_list',
            'title' => __('Fillable template list', 'pdfforms').$messageHelper->getMessageWithIcon('pdfFormCreation.fillableTemplateList.general'),
            'postType' => PDFForm::PDFFORMS_POST_TYPE,
            'context' => 'side',
            'priority' => 1,
            'fields' => [
                [
                    'name' => 'fillable_template_list',
                    'type' => 'pickerPopup',
                    'popup-main-div-header' => __('Select template', 'pdfforms').$messageHelper->getMessageWithIcon('pdfFormCreation.fillableTemplateList.generalInPopup') ,
                    'list' => $documents,
                    'default' => $defaultDocument,
                ],
            ],
        ]);

        MetaBoxesFacade::make([
            'slug' => 'pdfform_send_mail',
            'title' => __('Send document to email', 'pdfforms').$messageHelper->getMessageWithIcon('pdfFormCreation.sendDocumentToEmail.general'),
            'postType' => PDFForm::PDFFORMS_POST_TYPE,
            'context' => 'normal',
            'priority' => 3,
            'fields' => [
                [
                    'label' => __('Send to admins emails', 'pdfforms'),
                    'name' => 'send_to_admin',
                    'type' => 'switcher',
                ],
                [
                    'label' => __('Send to email from field', 'pdfforms'),
                    'name' => 'send_to_field_email',
                    'type' => 'switcher',
                ],
                [
                    'label' => __('Email field', 'pdfforms'),
                    'name' => 'email_field',
                    'type' => 'input',
                ],
                [
                    'label' => __('Send to custom emails', 'pdfforms'),
                    'name' => 'custom_emails',
                    'type' => 'input',
                ],
            ],
        ]);

        MetaBoxesFacade::make([
            'slug' => 'pdfform_submit_location',
            'title' => __('Submit button location', 'pdfforms').$messageHelper->getMessageWithIcon('pdfFormCreation.submitButtonLocation'),
            'postType' => PDFForm::PDFFORMS_POST_TYPE,
            'context' => 'normal',
            'priority' => 3,
            'fields' => [
                [
                    'name' => 'pdfform_submit_location',
                    'type' => 'select',
                    'list' => [
                        'bottom' => __('Bottom', 'pdfforms'),
                        'top' => __('Top', 'pdfforms'),
                    ],
                ],
            ],
        ]);

        MetaBoxesFacade::make([
            'slug' => 'pdfform_submit_message',
            'title' => __('Submit message', 'pdfforms').$messageHelper->getMessageWithIcon('pdfFormCreation.submitMessage'),
            'postType' => PDFForm::PDFFORMS_POST_TYPE,
            'context' => 'normal',
            'priority' => 3,
            'fields' => [
                [
                    'name' => 'pdfform_submit_message',
                    'type' => 'input',
                ],
            ],
        ]);

        MetaBoxesFacade::make([
            'slug' => 'pdfform_message_success',
            'title' => __('Success message', 'pdfforms').$messageHelper->getMessageWithIcon('pdfFormCreation.successMessage'),
            'postType' => PDFForm::PDFFORMS_POST_TYPE,
            'context' => 'normal',
            'priority' => 3,
            'fields' => [
                [
                    'name' => 'pdfform_message_success',
                    'type' => 'input',
                ],
            ],
        ]);

        MetaBoxesFacade::make([
            'slug' => 'pdfform_message_fail',
            'title' => __('Fail message', 'pdfforms').$messageHelper->getMessageWithIcon('pdfFormCreation.failMessage'),
            'postType' => PDFForm::PDFFORMS_POST_TYPE,
            'context' => 'normal',
            'priority' => 3,
            'fields' => [
                [
                    'name' => 'pdfform_message_fail',
                    'type' => 'input',
                ],
            ],
        ]);
    }

    /**
     * @return bool
     */
    private function checkPostType()
    {
        $requestService = CustomRequest::instance();
        $phpSelf = $requestService->server(self::SERVER_VARIABLE_PHP_SELF);

        if (!is_array($requestService->get('post')) && $requestService->get('post') > 0 && get_post(strip_tags($requestService->get('post')))->post_type === PDFForm::PDFFORMS_POST_TYPE) {
            return true;
        }
        if ($requestService->get('post_type') === PDFForm::PDFFORMS_POST_TYPE && $phpSelf == self::SERVER_VARIABLE_PHP_SELF_VALUE) {
            return true;
        }
        if (strip_tags($requestService->post('post_type')) === PDFForm::PDFFORMS_POST_TYPE) {
            return true;
        }

        return false;
    }
}
