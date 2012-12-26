(function ($) {

  Drupal.behaviors.tmgmt_local = {
    attach: function (context, settings) {

      var remoteCheckBox = $('#edit-roles-' + settings.tmgmt_local.tmgmt_server_tc_rid);
      var translatorCheckBox = $('#edit-roles-' + settings.tmgmt_local.tmgmt_local_ts_rid);
      var skillsContainer = $('#edit-tmgmt-translation-skills');

      // Deal with initial states
      if (remoteCheckBox.attr('checked')) {
        translatorCheckBox.attr('checked', false);
        translatorCheckBox.attr('disabled', true);
        skillsContainer.hide();
      }

      if (translatorCheckBox.attr('checked')) {
        remoteCheckBox.attr('checked', false);
        remoteCheckBox.attr('disabled', true);
        remoteCheckBox.attr('disabled', true);
        skillsContainer.show();
      }
      else {
        skillsContainer.hide();
      }

      // Deal with user interaction
      translatorCheckBox.click(function() {
        if ($(this).attr('checked')) {
          remoteCheckBox.attr('checked', false);
          remoteCheckBox.attr('disabled', true);
          skillsContainer.slideDown();
        }
        else {
          remoteCheckBox.attr('disabled', false);
          skillsContainer.slideUp();
        }
      });
      remoteCheckBox.click(function() {
        if ($(this).attr('checked')) {
          translatorCheckBox.attr('checked', false);
          translatorCheckBox.attr('disabled', true);
        }
        else {
          translatorCheckBox.attr('disabled', false);
        }
      });
    }
  };

})(jQuery);
