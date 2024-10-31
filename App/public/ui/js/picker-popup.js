jQuery(document).ready(function () {
  let picker;
  let currentTemplateInServer;

  let initializeSelectPicker = function (isDisabled = false) {
    picker = jQuery('.select2-picker').select2({
      dropdownParent: jQuery('.modal-select-div'),
      placeholder: isDisabled ? 'Updating' : "Search",
      disabled: isDisabled,
      sorter: function (data) {
        return data.sort(function (a, b) {
          if (a.id < b.id) {
            return 1;
          } else if (a.id > b.id) {
            return -1;
          }
          return 0;
        });
      }
    })
      .on("select2:open", function () {
        jQuery(".select2-search__field").attr("placeholder", "Search");
      })
      .on("select2:close", function () {
        jQuery(".select2-search__field").attr("placeholder", null);
      });
  };

  let setFreshSelectList = function () {
    let currentValue = jQuery('.select2-picker').val();
    jQuery('.select2-picker option').remove();
    initializeSelectPicker(true);

    jQuery.ajax({
      method: 'GET',
      data: {
        action: 'fresh_templates',
        postId: PdfFormGlobalVariables.pdffiller_form_post_id,
      }
    })
      .done(function (listOfFillableTemplates) {
        if (jQuery(listOfFillableTemplates).length > 0) {
          jQuery.each(listOfFillableTemplates, function (index, value) {
            jQuery('.select2-picker').append(
              '<option value="' + index + '">' + value + '</option>'
            );
          });
        } else {
          jQuery.magnificPopup.open({
            items: {
              src: jQuery('<div class="white-popup-block">' +
                '<div class="warning-message">This template has no fillable fields. Please choose template with at least one fillable field.</div>' +
                '</div>'),
              type: 'inline'
            }
          });
        }

        initializeSelectPicker();
        picker.val(currentValue).trigger('change.select2');
      })
      .fail(function (error) {
        console.log(error.responseText);
        initializeSelectPicker();
        picker.val(currentValue).trigger('change.select2');
      });
  };

  let setAllSelectList = function () {
    let currentValue = jQuery('.select2-picker').val();
    jQuery('.select2-picker option').remove();
    initializeSelectPicker(true);

    jQuery.ajax({
      method: 'GET',
      data: {
        action: 'all_templates',
      }
    })
      .done(function (listOfFillableTemplates) {
        jQuery.each(listOfFillableTemplates, function (index, value) {
          jQuery('.select2-picker').append(
            '<option value="' + index + '">' + value + '</option>'
          );
        });
        initializeSelectPicker();
        picker.val(currentValue).trigger('change.select2');
      })
      .fail(function (error) {
        console.log(error.responseText);
        initializeSelectPicker();
        picker.val(currentValue).trigger('change.select2');
      });
  };

  let setFillableFields = function () {
    let currentValue = jQuery('.select2-picker').val();
    initializeSelectPicker(true);

    jQuery.ajax({
      method: 'GET',
      data: {
        templateId: currentValue,
        postId: window.PdfFormGlobalVariables.pdffiller_form_post_id,
        action: 'template_fillable_fields'
      }
    })
      .done(function (listOfFillableFields) {
        if (jQuery(listOfFillableFields).length > 0) {
          PdfFormGlobalVariables.pdfforms_button.fields = listOfFillableFields;
          PdfFormGlobalVariables.pdfforms_button.fieldsList = _.map(PdfFormGlobalVariables.pdfforms_button.fields, function (field) {
            return {
              "text": field.text, "value": field.text
            };
          });
          jQuery('.mfp-close').click();
        } else {
          jQuery.magnificPopup.open({
            items: {
              src: jQuery('<div class="white-popup-block">' +
                '<div class="warning-message">This template does not have fillable fields. Please choose another fillable template.</div>' +
                '</div>'),
              type: 'inline'
            }
          });
        }

        initializeSelectPicker();
        currentTemplateInServer = currentValue;
        picker.val(currentValue).trigger('change.select2');
      })
      .fail(function (error) {
        console.log(error.responseText);
        initializeSelectPicker();
        picker.val(currentValue).trigger('change.select2');
      });
  };

  jQuery.ajaxSetup({
    url: window.PdfFormGlobalVariables.ajax_url,
    dataType: 'json',
  });

  initializeSelectPicker();
  currentTemplateInServer = jQuery('.select2-picker').val();

  jQuery('.popup-main-picker').magnificPopup({
    type: 'inline',
    callbacks: {
      open: function () {
        jQuery('.mce-close').click();
      },
    }
  });

  jQuery('#main-select-button').click(function () {
    if (currentTemplateInServer !== jQuery('.select2-picker').val()) {
      tinyMCE.activeEditor.setContent('');
      setFillableFields();
    }
  });

  jQuery('#first-additional-button').click(function () {
    setFreshSelectList();
  });

  jQuery('#second-additional-button').click(function () {
    setAllSelectList();
  });
});
