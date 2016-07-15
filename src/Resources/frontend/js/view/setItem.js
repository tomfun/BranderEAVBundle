import StateItemView from './stateItemView';


export default StateItemView.extend({
  'tagName':           'li',
  'templateName':      '@BranderEAV/Widgets/set.item.twig',
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

});
