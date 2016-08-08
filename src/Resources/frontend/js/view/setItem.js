import StateItemView from './stateItemView';
import {render as template} from 'templates/brander-eav/Widgets/set.item.twig';

export default StateItemView.extend({
  template,

  tagName:           'li',
  changeStateEvents: [
    'processing',
    'error',
    'saved',
  ],

  ui: {
    edit:   '.edit',
    remove: '.remove',
  },

  triggers: {
    'click @ui.edit':   'select',
    'click @ui.remove': 'remove',
  },
});
