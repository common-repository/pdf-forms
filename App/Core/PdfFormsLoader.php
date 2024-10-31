<?php

namespace PdfFormsLoader\Core;

use PdfFormsLoader\Models\{IntegrationsSettingsModel, PDFForm};
use PdfFormsLoader\Modules\PdfFormsPopupAjax;
use PdfFormsLoader\Shortcodes\{Shortcodes, FillableFormShortcode};
use PdfFormsLoader\Integrations\IntegrationFabric;

/**
 * Class PdfFormsLoader
 * @package PdfFormsLoader\Core
 */
class PdfFormsLoader
{
    public static $requestService;

    private $autoload = [
        'PdfFormsLoader\Modules\TinymceButtons',
        'PdfFormsLoader\Modules\AdminMenu',
        'PdfFormsLoader\Modules\PostTypes',
        'PdfFormsLoader\Modules\PdfFormMetaboxes',
        'PdfFormsLoader\Modules\GeneralPdfFormsAssets',
        'PdfFormsLoader\Modules\PdfFormsPopupAjax',
    ];

    public function __construct()
    {
        $this->autoload();

        $this->runIntegrations();
        $this->addShortcodes();
        $this->changeTemplateForPdfFormPosts();
        $this->addWidgets();

        add_action('admin_init', [$this, 'assignAsyncEvents']);
    }

    protected function autoload()
    {
        if (count($this->autoload)) {
            foreach ($this->autoload as $class) {
                new $class;
            }
        }
    }

    public function assignAsyncEvents()
    {
        $ajaxModule = new PdfFormsPopupAjax();
        add_action('wp_ajax_template_fillable_fields', [&$ajaxModule, 'bindTemplateAndGetFillableFields']);
        add_action('wp_ajax_fresh_templates', [&$ajaxModule, 'freshTemplates']);
        add_action('wp_ajax_all_templates', [&$ajaxModule, 'allTemplates']);

        $fillableFormShortcode = new FillableFormShortcode();
        add_action('wp_ajax_pdfformsave', [&$fillableFormShortcode, 'fillableSave']);
        add_action('wp_ajax_nopriv_pdfformsave', [&$fillableFormShortcode, 'fillableSave']);

        JsVariables::addVariable('ajax_url', admin_url('admin-ajax.php'));
    }

    protected function addShortcodes()
    {
        $shortcodes = new Shortcodes();
        $shortcodes->initShortcodes(['FormsFields', 'FillableForm']);
    }

    private function addWidgets()
    {
        add_action('widgets_init', function () {
            register_widget('PdfFormsLoader\Widgets\PdfFormWidget');
            register_widget('PdfFormsLoader\Widgets\EmbeddedJsClientWidget');
        });
    }

    protected function runIntegrations()
    {
        add_filter('pdfform_integrations', [$this, 'getIntegrationsList'], 40, 4);
    }

    public function getIntegrationsList($integrations)
    {
        if (IntegrationsSettingsModel::getCF7Setting() == 'true') {
            $integrations['custom-form-7'] = IntegrationFabric::getIntegration('Contact7Form');
        }

        return $integrations;
    }

    private function changeTemplateForPdfFormPosts()
    {
        add_action('single_template', function ($single_template) {
            global $post;

            if ($post->post_type == PDFForm::PDFFORMS_POST_TYPE) {
                $post->post_content = do_shortcode('[pdfform id="'.$post->ID.'"]');
                remove_filter('the_content', 'wpautop');
                remove_filter('the_excerpt', 'wpautop');
            }

            return $single_template;
        });
    }
}
