import StateItemView from './stateItemView';
import {render as template} from 'templates/brander-eav/Widgets/attribute.item.twig';


export default StateItemView.extend({
  template,

  'tagName':           'li',
  'changeStateEvents': [
    'processing',
    'error',
    'saved',
  ],

  'ui': {
    'edit':   '.edit',
    'remove': '.remove',
  },

  'triggers': {
    'click @ui.edit':   'select',
    'click @ui.remove': 'remove',
  },

  serializeData() {
    const first = this.model.get('translations').find({locale: this.options.currentLocale});
    return {
      currentLocale: this.options.currentLocale,
      // lcl:           this.model.get('translations').indexOfLocale(this.currentLocale),
      title:         first ? first.get('title') : '',
      model:         this.model,
    };
  },
});
