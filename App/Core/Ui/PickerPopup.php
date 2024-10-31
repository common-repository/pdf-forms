<?php
namespace PdfFormsLoader\Core\Ui;

use PdfFormsLoader\Core\{Assets, Views};

class PickerPopup
{
    private $default_settings;

    public $settings;

    /**
     * Init base settings
     * @param null $attr
     */
    public function __construct($attr = null)
    {
        $this->default_settings = [
            'popup-main-div-attributes' => [
                'id' => 'pick-template-modal',
                'class' => 'white-popup-block mfp-hide',
                '_lpchecked' => "1",
            ],
            'popup-main-div-header' => __('Choose new template', 'pdfforms'),
            'open-popup-link-href' => '#pick-template-modal',
            'open-popup-link-text' => __('Select template', 'pdfforms'),
            'button-text' => __('Select template', 'pdfforms'),
            'first-additional-button-text' => __('Refresh list', 'pdfforms'),
            'second-additional-button-text' => __('View all documents', 'pdfforms'),
            'list' => [],
            'default' => null,
        ];

        if (empty($attr) || !is_array($attr)) {
            $attr = $this->default_settings;
        } else {
            foreach ($this->default_settings as $key => $value) {
                if (empty($attr[$key])) {
                    $attr[$key] = $this->default_settings[$key];
                }
            }
        }

        $this->settings = $attr;
    }

    /**
     * Add styles
     */
    private function assets()
    {
        $assets = new Assets();

        wp_enqueue_style(
            'select2',
            $assets->getCssUrl('select2.css', 'select2'),
            array(),
            '1.0.0',
            'all'
        );

        wp_enqueue_script(
            'select2',
            $assets->getJsUrl('select2.full.js', 'select2'),
            array(),
            '1.0.0',
            'all'
        );

        wp_enqueue_style(
            'magnific-popup',
            $assets->getCssUrl('magnific-popup.css', 'magnific-popup'),
            array(),
            '1.0.0',
            'all'
        );

        wp_enqueue_script(
            'magnific-popup',
            $assets->getJsUrl('jquery.magnific-popup.min.js', 'magnific-popup'),
            array(),
            '1.0.0',
            'all'
        );

        wp_enqueue_script(
            'picker-popup',
            $assets->getJsUrl('picker-popup.js', 'ui'),
            array(),
            '1.0.0',
            'all'
        );
    }

    /**
     * Render html
     *
     * @return string
     */
    public function output()
    {
        $this->assets();

        if (!empty($this->settings['datalist']) && empty($this->settings['list'])) {
            $this->settings['list'] = $this->settings['datalist'];
        }

        if (is_string($this->settings['list'])) {
            $list = explode(',', $this->settings['list']);
            $newList = [];
            foreach ($list as $key => $item) {
                $newList[$item] = $item;
            }
            $list = $newList;
            unset($newList);
        }

        if (is_array($this->settings['list'])) {
            $list = $this->settings['list'];
        }

        $popupMainDivAttributes = '';
        foreach ($this->settings['popup-main-div-attributes'] as $attributeName => $attributeValue) {
            $popupMainDivAttributes .= ' '.$attributeName.'="'.$attributeValue.'"';
        }

        ksort($list);

        $html = Views::render(
            'popups/picker-popup.php',

            array(
                'popup_main_div_header' => $this->settings['popup-main-div-header'],
                'open_popup_link_href' => $this->settings['open-popup-link-href'],
                'open_popup_link_text' => $this->settings['open-popup-link-text'],
                'button_text' => $this->settings['button-text'],
                'first_additional_button_text' => $this->settings['first-additional-button-text'],
                'second_additional_button_text' => $this->settings['second-additional-button-text'],
                'popup_main_div_attributes' => $popupMainDivAttributes,
                'list' => $list,
                'default' => $this->settings['default'],
            )
        );

        return $html;
    }

}
