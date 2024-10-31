<?php
namespace PdfFormsLoader\Helpers;

use PdfFormsLoader\Core\Assets;

class DescriptionMessageHelper
{
    const MESSAGES_CLASS_NAME_SUFFIX = 'Messages';

    /**
     * @param string $elementIndex
     * @return mixed
     */
    public function getMessageWithIcon(string $elementIndex)
    {
        if ($message = $this->getMessage($elementIndex)) {
            return  " <i class=\"fa fa-question-circle\" title=\"$message\"></i>";
        }

        return false;
    }

    /**
     * @param string $elementIndex
     * @return mixed
     */
    public function getMessage(string $elementIndex)
    {
        add_action( 'admin_enqueue_scripts', array( &$this, 'assets') );

        $message = array_get((new Messages())->getMessageList(), $elementIndex);

        if (is_string($message)) {
            return $message;
        }

        return false;
    }

    public function assets()
    {
        $assets = new Assets();

        wp_enqueue_style(
            'font-awesome',
            '//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css'
        );

        wp_enqueue_style(
            'font-awesome',
            '//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css'
        );

        wp_enqueue_script(
            'custom-tooltips',
            $assets->getJsUrl( 'tooltips.js', 'ui'),
            array( 'jquery', 'jquery-ui'),
            '1.0.0',
            true
        );
    }
}
