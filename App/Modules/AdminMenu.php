<?php

namespace PdfFormsLoader\Modules;

use PdfFormsLoader\Facades\PageBuilderFacade;
use PdfFormsLoader\Helpers\DescriptionMessageHelper;
use PdfFormsLoader\Integrations\IntegrationFabric;
use PdfFormsLoader\Models\PDFFillerModel;
use PdfFormsLoader\Services\PdfFillerApiService;

class AdminMenu
{
    protected $defaultSettings = [
        'pdfforms-main-messages' => [
            'message-success' => 'Success',
            'message-fail' => 'Fail',
            'submit-message' => 'Submit',
        ],
        'pdfforms-main-integrations' => [
            'contact-7-form' => 'false',
        ],
        'pdfforms-mail' => [
            'subject' => 'PDFForm attachment',
            'message' => 'You can download pdf file',
        ],
    ];

    public function __construct()
    {
        $this->addAdminMenu();
    }

    protected function addAdminMenu()
    {
        $this->checkEmptySettings();

        $messageHelper = new DescriptionMessageHelper();

        $settings['pdfforms-main-settings'][] = array(
            'type' => 'switcher',
            'slug' => 'token-exist',
            'title' => __('Authorization status', 'pdfforms')
                .$messageHelper->getMessageWithIcon('settings.main.validLoginData'),
            'field' => array(
                'id' => 'token-exist',
                'value' => PDFFillerApiService::getInstance()->isTokenValid() ? 'true' : 'false',
                'disabled' => true,
            ),
        );

        $settings['pdfforms-main-settings'][] = array(
            'type' => 'input',
            'slug' => 'pdffiller-oauth-token',
            'title' => __('API key', 'pdfforms')
                .$messageHelper->getMessageWithIcon('settings.main.oAuthToken'),
            'field' => array(
                'id' => 'pdffiller-oauth-token',
                'value' => '',
            ),
        );

        $settings['pdfforms-main-messages'][] = array(
            'type' => 'input',
            'slug' => 'message-success',
            'title' => __('Success message', 'pdfforms'),
            'field' => array(
                'id' => 'pdfforms-message-success',
                'value' => 'Fillable form have been completed',
            ),
        );

        $settings['pdfforms-main-messages'][] = array(
            'type' => 'input',
            'slug' => 'message-fail',
            'title' => __('Fail message', 'pdfforms'),
            'field' => array(
                'id' => 'pdfforms-message-fail',
                'value' => 'Fillable form can`t completed',
            ),
        );

        $settings['pdfforms-main-messages'][] = array(
            'type' => 'input',
            'slug' => 'submit-message',
            'title' => __('Submit message', 'pdfforms'),
            'field' => array(
                'id' => 'pdfforms-submit-message',
                'value' => 'Send',
            ),
        );

        $settings['pdfforms-mail'][] = array(
            'type' => 'input',
            'slug' => 'subject',
            'title' => __('Subject text', 'pdfforms'),
            'field' => array(
                'id' => 'pdfforms-mail-subject',
                'value' => 'PDFForm attachment',
            ),
        );

        $settings['pdfforms-mail'][] = array(
            'type' => 'input',
            'slug' => 'message',
            'title' => __('Message text', 'pdfforms'),
            'field' => array(
                'id' => 'pdfforms-mail-message',
                'value' => 'You can download pdf file',
            ),
        );

        $Contact7Form = IntegrationFabric::getIntegration('Contact7Form');

        if ($Contact7Form->checker()) {
            $settings['pdfforms-main-integrations'][] = array(
                'type' => 'switcher',
                'slug' => 'contact-7-form',
                'title' => __('Contact 7 form', 'contact-form-7'),
                'field' => array(
                    'id' => 'contact-7-form',
                    'value' => 'false',
                ),
            );
        }

        $devAndSupportLinks = $this->formSupportLink();

        PageBuilderFacade::makePageMenu('pdfforms-settings', 'Settings', 'edit.php?post_type=pdfforms')
            ->set(
                array(
                    'capability' => 'manage_options',
                    'position' => 22,
                    'icon' => 'dashicons-admin-site',
                    'before' => $devAndSupportLinks,
                    'sections' => array(
                        'pdfforms-main-settings' => array(
                            'slug' => 'pdfforms-main-settings',
                            'name' => __('Main', 'pdfforms')
                                .$messageHelper->getMessageWithIcon('settings.main.general'),
                            'tab-name' => __('Main', 'pdfforms'),
                            'description' => '',
                            'submit-button-text' => __('Save main settings', 'pdfforms'),
                        ),
                        'pdfforms-main-messages' => array(
                            'slug' => 'pdfforms-main-messages',
                            'name' => __('Messages', 'pdfforms')
                                .$messageHelper->getMessageWithIcon('settings.messages.general'),
                            'tab-name' => __('Messages', 'pdfforms'),
                            'description' => '',
                            'submit-button-text' => __('Save messages', 'pdfforms'),
                        ),
                        'pdfforms-mail' => array(
                            'slug' => 'pdfforms-mail',
                            'name' => __('Mail', 'pdfforms')
                                .$messageHelper->getMessageWithIcon('settings.mail.general'),
                            'tab-name' => __('Mail', 'pdfforms'),
                            'description' => '',
                            'submit-button-text' => __('Save mail settings', 'pdfforms'),
                        ),
                        'pdfforms-main-integrations' => array(
                            'slug' => 'pdfforms-main-integrations',
                            'name' => __('Integrations', 'pdfforms')
                                .$messageHelper->getMessageWithIcon('settings.integrations.general'),
                            'tab-name' => __('Integrations', 'pdfforms'),
                            'description' => '',
                            'submit-button-text' => __('Save integrations', 'pdfforms'),
                        ),
                    ),
                    'settings' => $settings,
                )
            );
    }

    private function checkEmptySettings()
    {
        foreach ($this->defaultSettings as $slugSection => $section) {
            $sectionValue = get_option($slugSection);
            if (empty($sectionValue)) {
                $sectionValue = $section;
            }
            $change = false;
            foreach ($section as $slugSetting => $settingValue) {
                if (empty($sectionValue[$slugSetting])) {
                    $sectionValue[$slugSetting] = $settingValue;
                    $change = true;
                }
            }
            if ($change) {
                update_option($slugSection, $sectionValue);
            }
        }
    }

    private function formSupportLink()
    {
        return "<p>".__('Plugin developer site - ')."<a href='".PDFFillerModel::PDFFILLER_SITE."' target='_blank'>PDFfiller</a></p>"
            ."<p>".__('Client support ')."<a href=".PDFFillerModel::PDFFILLER_HELP_LINK." target='_blank'>support@pdffiller.com</a></p>";
    }
}
