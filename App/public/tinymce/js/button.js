tinymce.PluginManager.add('pdfforms_button', function (editor, url) {

  if (!window.PdfFormGlobalVariables.pdffiller_form_post) {
    return;
  }

  PdfFormGlobalVariables.pdfforms_button.fieldsList = _.map(PdfFormGlobalVariables.pdfforms_button.fields, function (field) {
    return {
      "text": field.text, "value": field.text
    };
  });

  // Add Button to Visual Editor Toolbar
  editor.addButton('pdfforms_button', {
    title: "Insert Button Link",
    cmd: "pdfform_insert_field",
    type: "button",
    tooltip: "Add fillable fields",
    //icon: "dashicon dashicons-no",
    image: window.PdfFormGlobalVariables.pdfforms_button.image_field
  });

  editor.addCommand('pdfform_insert_field', function () {
    // Calls the pop-up modal
    editor.windowManager.open({
      // Modal settings
      title: 'Insert form field',
      width: jQuery(window).width() * 0.3,
      // minus head and foot of dialog box
      height: (jQuery(window).height() - 36 - 50) * 0.15,
      id: 'plugin-slug-insert-dialog',
      body: [
        {
          type: 'listbox',
          id: 'fields-list',
          name: 'fields_list',
          label: 'Choose a field',
          values: PdfFormGlobalVariables.pdfforms_button.fieldsList
        }
      ],
      buttons: [
        {
          text: 'Insert',
          onclick: function (e) {
            var fieldKey = jQuery('#fields-list button').text().replace(" ", "");

            var field = PdfFormGlobalVariables.pdfforms_button.fields[fieldKey];

            var shortcodeAttr = '';
            _.each(field.fieldAttr, function (value, key) {
              shortcodeAttr = shortcodeAttr + ' ' + key + '="' + value + '"';
            }, shortcodeAttr);

            var shortcode = '[pdfformfield ' + shortcodeAttr + ']';
            editor.insertContent(shortcode);
          }
        },
        {
          text: 'Cancel',
          id: 'plugin-slug-button-cancel',
          onclick: 'close'
        }
      ],
    });
  });

});


tinymce.PluginManager.add('pdfforms_list_button', function (editor, url) {

  if (window.PdfFormGlobalVariables.pdffiller_form_post) {
    return;
  }

  window.PdfFormGlobalVariables.pdfforms_list_button.documents = _.map(PdfFormGlobalVariables.pdfforms_list_button.documents, function (document) {
    document.onclick = function (e) {
      editor.insertContent(
        '[pdfform id="' + document.id + ' "]'
      );
    };
    return document;
  });

  // Add Button to Visual Editor Toolbar
  editor.addButton('pdfforms_list_button', {
    title: "Insert Button Link",
    cmd: "pdfform_insert_form",
    type: "button",
    tooltip: "Add fillable form",
    //icon: "dashicon dashicons-no",
    image: window.PdfFormGlobalVariables.pdfforms_button.image_form
  });

    editor.addCommand( 'pdfform_insert_form', function() {
        // Calls the pop-up modal
        editor.windowManager.open({
            // Modal settings
            title: 'Insert form',
            width: jQuery( window ).width() * 0.4,
            // minus head and foot of dialog box
            height: (jQuery( window ).height() - 36 - 50) * 0.4,
            inline: 1,
            id: 'plugin-slug-insert-dialog-form',
            body: PdfFormGlobalVariables.pdfforms_list_button.documents,
            buttons: [
                {
                    text: 'Cancel',
                    id: 'plugin-slug-button-cancel',
                    onclick: 'close'
                }
            ],
        });
    });

});

tinymce.PluginManager.add('pdfforms_help_button', function (editor, url) {

  // Add Button to Visual Editor Toolbar
  editor.addButton('pdfforms_help_button', {
    cmd: "pdfform_get_help",
    type: "button",
    tooltip: "How to use PDFForms",
    icon: "fa-question-circle",
  });

  editor.addCommand('pdfform_get_help', function () {
    tinymce.activeEditor.notificationManager.open({
      text: PdfFormGlobalVariables.pdffiller_get_help_tiny_mce_popup,
      type: 'info'
    });
  });
});


