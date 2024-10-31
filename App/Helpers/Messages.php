<?php
namespace PdfFormsLoader\Helpers;

use PdfFormsLoader\Core\Assets;

class Messages
{
    public function getMessageList(): array
    {
        return [
            'pdfFormCreation' => [
                'general' => __("<p class='bold-font'>How to embed fillable forms on your website with PDFForms.</p>"
                    ."<ol>"
                    ."<li>Go to <span class='bold-font'>PDFforms > Settings</span> to connect PDFForms plugin to your PDFfiller account. <br>"
                      ."Set any other preferences you may have and save them.</li>"
                    ."<li>Go to <span class='bold-font'>PDFForms > Add new</span>.</li>"
                    ."<li>Enter a name for your form and click <span class='bold-font'>Select template</span> "
                      ."on the right to choose a fillable form in your PDFfiller account. </li>"
                    ."<li>Click <span class='bold-font'>Add fillable fields</span> <img src=".Assets::getImageUrlStatic('field.png', 'tinymce')." />"
                      ." in the tools panel. A list of all fillable fields in the form will be displayed.<br>"
                      ." The field names are the same as those you set in PDFfiller.</li>"
                    ."<li>Select the field you want to insert in your form and click <span class='bold-font'>Insert</span>.</li>"
                    ."<li>Once you’ve added all necessary fields, click <span class='bold-font'>Cancel</span>.</li>"
                    ."<li>Set up other options for a given form: enter an email address to send a submitted form, <br>"
                      ."choose a location for the <span class='bold-font'>Submit</span> button and enter texts for success and fail messages.</li>"
                    ."<li> Click <span class='bold-font'>Publish</span>.</li>"
                    ."<li>To embed a newly created form on your page or post, click <span class='bold-font'>Add Fillable Form</span> "
                      ."<img src=".Assets::getImageUrlStatic('form2.png', 'tinymce')." height='16px'/> in the tools panel <br> and select the form you want to add.</li>"
                    ."<li> Insert the shortcode of the form anywhere in the content."
                    ."</ol>", 'pdfforms'),
                'sendDocumentToEmail' => [
                    'general' => __("Enter the email address(es) where you'd like submitted forms to be sent. "
                        ."Please also customize your WordPress email settings. ", 'pdfforms'),
                ],
                'fillableTemplateList' => [
                    'general' => __('Select a fillable template.', 'pdfforms'),
                    'generalInPopup' => __('Choose the form you need from the dropdown and click <b>Select template</b>. '
                        .'The dropdown list will display the last 100 fillable forms you\'ve edited on your PDFfiller account. '
                        .'If you can\'t find the form you need, click <b>Refresh list</b>. '
                        .'Click <b>View all documents</b> to display all fillable documents saved to your account.', 'pdfforms'),
                ],
                'submitButtonLocation' =>  __('Choose where you\'d like to place the Submit button on the page.', 'pdfforms'),
                'submitMessage' => __('Name your submit button.', 'pdfforms'),
                'successMessage' => __('This message is shown once a form has been sucessfully submitted.', 'pdfforms'),
                'failMessage' => __('This message is shown if a problem occured during form submission.', 'pdfforms'),
            ],

            'settings' => [
                'general' => __('Set PDFfiller plugin settings on this page.)', 'pdfforms'),
                'main' => [
                        'general' => __('Enter your API key to connect PDFForms to your website.', 'pdfforms'),
                    'validLoginData' => __('If the button is green, your API key is valid. If the button is grey, please enter valid authorization data.    ', 'pdfforms'),
                    'oAuthToken' => __('Copy your API V2 key here: https://developers.pdffiller.com/#tab-settings-settings', 'pdfforms'),
                ],
                'messages' => [
                    'general' => __('Customize text for successful and failed form submissions.', 'pdfforms'),
                ],
                'mail' => [
                    'general' => __('Customize the subject and text for your submitted form email.', 'pdfforms'),
                ],
                'integrations' => [
                    'general' => __('Select an available integration to connect with PDFForms.', 'pdfforms'),
                ]
            ]
        ];
    }
}
