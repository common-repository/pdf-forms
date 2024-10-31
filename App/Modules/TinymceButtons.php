<?php

namespace PdfFormsLoader\Modules;

use PdfFormsLoader\Core\{Assets, CustomRequest, JsVariables, Ui\FieldsMapper};
use PdfFormsLoader\Facades\TinymceButtonsFacade;
use PdfFormsLoader\Helpers\DescriptionMessageHelper;
use PdfFormsLoader\Services\PdfFillerApiService;

class TinymceButtons
{
    public function __construct()
    {
        $this->addButtons();
    }

    private function addButtons()
    {
        $template = $this->getFillableTemplateFields();

        $fieldsMapper = new FieldsMapper();
        $messageHelper = new DescriptionMessageHelper();

        $fields = [];
        foreach ($template as $field) {
            $fields[$field->name] = (object)[
                'fieldAttr' => $fieldsMapper->prepareShortCodeAttr($field),
                'text' => $field->name,
                'type' => 'button',
            ];
        }

        JsVariables::addVariable('pdfforms_button', [
            'image_form' => Assets::getImageUrlStatic('form2.png', 'tinymce'),
            'image_field' => Assets::getImageUrlStatic('field.png', 'tinymce'),
            'fields' => $fields,
        ]);

        TinymceButtonsFacade::buttonsFactory([
            'button_name' => 'pdfforms_button',
            'post_types' => ['pdfforms', 'post', 'page'],
            'assets' => [
                'scripts' => [
                    [
                        'name' => 'pdfforms_button',
                        'file' => 'button.js',
                        'parent' => ['jquery'],
                        'footer' => true,
                        'version' => '2.0',
                    ],
                ],
            ],
        ])->makeButton();


        $posts = get_posts(['post_type' => 'pdfforms', 'numberposts' => 200]);
        $templates = [];
        foreach ($posts as $post) {
            $templates[] = (object)[
                'type' => 'button',
                'text' => $post->post_title,
                'id' => $post->ID,
                'class' => 'pdfform-editor-button',
            ];
        }

        JsVariables::addVariable('pdfforms_list_button', [
            'documents' => $templates,
        ]);

        TinymceButtonsFacade::buttonsFactory([
            'button_name' => 'pdfforms_list_button',
            'post_types' => ['post', 'page'],
            'assets' => [
                'scripts' => [
                    [
                        'name' => 'pdfforms_list_button',
                        'file' => 'button.js',
                        'parent' => ['jquery'],
                        'footer' => true,
                        'version' => '2.0',
                    ],
                ],
            ],
        ])->makeButton();

        TinymceButtonsFacade::buttonsFactory([
            'button_name' => 'pdfforms_help_button',
            'post_types' => ['pdfforms', 'post', 'page'],
            'assets' => [
                'scripts' => [
                    [
                        'name' => 'pdfforms_help_button',
                        'file' => 'button.js',
                        'parent' => ['jquery'],
                        'footer' => true,
                        'version' => '2.0',
                    ],
                ],
            ],
        ])->makeButton();

        JsVariables::addVariable('pdffiller_get_help_tiny_mce_popup',
            $messageHelper->getMessage('pdfFormCreation.general'));
    }

    private function getFillableTemplateFields(): array
    {
        $postId = CustomRequest::instance()->get('post');
        if (empty($postId)) {
            return [];
        }

        $templateId = (int)get_post_meta((int)$postId, 'fillable_template_list_fillable_template_list', true);
        if (empty($templateId)) {
            return [];
        }

        $dictionary = PDFFillerApiService::getInstance()->getFieldsWithCaching($templateId);
        $template = [];
        foreach ($dictionary as $key => $field) {
            $template[] = $field;
        }

        return $template;
    }
}
