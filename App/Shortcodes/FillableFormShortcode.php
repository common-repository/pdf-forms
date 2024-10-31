<?php
namespace PdfFormsLoader\Shortcodes;

use PdfFormsLoader\Core\{Views, Assets, JsVariables};
use PdfFormsLoader\Models\{TextsSettingsModel, PostMetaModel};
use PdfFormsLoader\Services\DocumentMailService;

class FillableFormShortcode
{
    public $slug = 'pdfform';
    public $initMethod = 'addShortcode';
    protected $postId;

    public function addShortcode($atts) {
        $atts = shortcode_atts( array(
            'id' => '0',
        ), $atts );

        $this->postId = $atts['id'];

        $post = get_post($atts['id']);

        if (empty($post)) {
            return Views::render(
                'shortcodes/errors/choose_page.php'
            );
        }

        $submitLocation = get_post_meta((int) $post->ID, 'pdfform_submit_location_pdfform_submit_location', true);
        empty($submitLocation) ? $submitLocation = 'bottom' : 'nothing';

        $submitMessage = get_post_meta((int) $post->ID, 'pdfform_submit_message_pdfform_submit_message', true);
        empty($submitMessage) ? $submitMessage = TextsSettingsModel::getSubmitMesage() : 'nothing';

        $this->assets();

        return Views::render(
            'shortcodes/fillableform.php',
            array(
                'id'              => $atts['id'],
                'content'         => apply_filters('the_content', $post->post_content),
                'submitLocation' => $submitLocation,
                'submitMessage' => $submitMessage,
            )
        );
    }

    public function fillableSave() {
        $fields = $_POST;
        foreach($fields as $key => $value) {

            if ($value === 'true'){
                $fields[$key] = 'ON';
            }

            if ($value === 'false'){
                $fields[$key] = 'OFF';
            }

        }

        $formId = $fields['pdfform-form-id'];
        $fillableTemplateId = get_post_meta((int) $formId, 'fillable_template_list_fillable_template_list', true);

        unset($fields['action'], $fields['pdfform-form-id']);

        $postModel = new PostMetaModel($formId);

        $emails = $postModel->getSendMailList($fields);

        $documentSender = new DocumentMailService();
        $result = $documentSender->sendDocument($fillableTemplateId, $fields, $emails);

        if (!$result) {
            wp_die('','', ['response' => 400]);
        }

        wp_die();
    }

    /**
     * Add styles and scripts
     *
     * @return void
     */
    public function assets() {
        $assets = new Assets();

        wp_enqueue_script( 'jquery-form' );

        $messageSuccess = get_post_meta((int)$this->postId, 'pdfform_message_success_pdfform_message_success', true);
        $messageFail = get_post_meta((int)$this->postId, 'pdfform_message_fail_pdfform_message_fail', true);

        empty($messageSuccess) ? $messageSuccess = TextsSettingsModel::getSuccessMesage() : $messageSuccess;
        empty($messageFail) ? $messageFail = TextsSettingsModel::getFailMesage() : $messageFail;

        JsVariables::addVariableFront('PdfformFillableForm_' . $this->postId, [
            'adminAjaxUrl' => admin_url( 'admin-ajax.php' ),
            'messageSuccess' => $messageSuccess,
            'messageFail' => $messageFail,
        ]);

        wp_enqueue_script(
            'pdfforms-shortcodes-form',
            $assets->getJsUrl( 'fillable-form.js', 'shortcodes' ),
            array( 'jquery' ),
            '1.0.0',
            true
        );


        wp_enqueue_style(
            'pdfforms-shortcodes-form',
            $assets->getCssUrl( 'fillable-form.css', 'shortcodes' ),
            array(),
            '1.0.0',
            'all'
        );
    }
}
