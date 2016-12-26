import Marionette from 'backbone.marionette';
import {render as template} from 'templates/brander-eav/Widgets/populate.twig';
import $ from 'jquery';
import router from 'router';
const PopulateView = Marionette.ItemView.extend({
  template,

  events: {
    'click .dt-populate-indexes': 'populate',
  },
  populate() {
    $.ajax({
      url:         router.generate('brander_eav_reindex'),
      type:        'PATCH',
      contentType: 'application/json',
      success:     this.showSuccessMessage,
      error:       this.showErrorMessage,
    });
  },
  showErrorMessage() {
    $('.dt-error-message')
      .fadeIn('slow')
      .delay('slow')
      .fadeOut('slow');
  },
  showSuccessMessage() {
    $('.dt-success-message')
      .fadeIn('slow')
      .delay('slow')
      .fadeOut('slow');
  },
});
export {PopulateView};
