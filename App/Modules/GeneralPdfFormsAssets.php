<?php
namespace PdfFormsLoader\Modules;

use PdfFormsLoader\Core\{Assets, CustomRequest};
use PdfFormsLoader\Models\{PDFFillerModel, PDFForm};

class GeneralPdfFormsAssets
{
    public function __construct()
    {
        $this->generalPluginSettings();
    }

    public function generalPluginAssets()
    {
        $assets = new Assets();

        wp_enqueue_style(
            'general-pdfforms',
            $assets->getCssUrl('general.css', 'tinymce'),
            array(),
            '1.0.0',
            'all'
        );
    }

    public function generalPluginAssetsForNonPdfForms()
    {
        $assets = new Assets();

        wp_enqueue_style(
            'general-pdfforms-for-admin-settings',
            $assets->getCssUrl('general-pdfforms-for-admin-settings.css', 'tinymce'),
            array(),
            '1.0.0',
            'all'
        );
    }

    private function generalPluginSettings()
    {
        $requestService = CustomRequest::instance();

        if ($requestService->get('post_type') === PDFForm::PDFFORMS_POST_TYPE ||
            ($requestService->get('post') > 0 && get_post($requestService->get('post'))->post_type === PDFForm::PDFFORMS_POST_TYPE)
        ) {
            add_filter('admin_footer_text', function () {
                echo $this->generateTextForFooter();
            });

            add_action( 'admin_enqueue_scripts', array( &$this, 'generalPluginAssets') );
        } else {
            add_action( 'admin_enqueue_scripts', array( &$this, 'generalPluginAssetsForNonPdfForms') );
        }
    }

    private function generateTextForFooter()
    {
        return '<span id="footer-note">'.__('Thank you for using ', 'pdfforms')
        .'<a href="'.PDFFillerModel::PDFFILLER_SITE.'" target="_blank">PDFfiller</a>'
        .__(' plugin.', 'pdfforms').'</span>';
    }
}
