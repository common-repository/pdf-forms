jQuery(document).ready(function () {
  let mouseLeaveTimer;

  jQuery('.fa.fa-question-circle').tooltip({
    hide: 500,
    content: function () {
      return jQuery(this).prop('title');
    },
    position: {my: "left top+15", at: "left bottom", collision: "flipfit"},
    open: function () {
      // make sure all other tooltips are closed when opening a new one
      jQuery('.fa.fa-question-circle').not(this).tooltip('close');
    }
  }).on('mouseleave', function (event) {
    let that = this;

    mouseLeaveTimer = setTimeout(function () {
      jQuery(that).tooltip('close');
    }, 500);

    event.stopImmediatePropagation();
  });

  jQuery(document).on('mouseenter', '.ui-tooltip', function () {
    // cancel tooltip closing on hover
    clearTimeout(mouseLeaveTimer);
  });

  jQuery(document).on('mouseleave', '.ui-tooltip', function () {
    // make sure tooltip is closed when the mouse is gone
    jQuery('.fa.fa-question-circle').tooltip('close');
  });
});
