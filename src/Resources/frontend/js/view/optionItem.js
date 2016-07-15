import Marionette from 'backbone.marionette';
import optionTemplate from 'templates/brander-eav/Widgets/option.item.twig';
import Backbone from 'backbone';
import 'backbone.modelbinder';


export default Marionette.ItemView.extend({
  'tagName':      'li',
  'templateName': '@BranderEAV/Widgets/option.item.twig',

  'initialize'(options) {
    this.currentLocale = options.currentLocale;
    this.template = optionTemplate,
      this.optionBinder = new Backbone.ModelBinder();
    this.on('processing', function () {
      this.state = 'edit';
      this.render();
    }, this);
    this.on('render', function () {
      if (this.state === 'edit') {
        this.optionBinder.bind(this.model, this.el);
      }
    }, this);
  },
  'ui':       {
    'remove': '.remove',
    'edit':   '.edit',
  },
  'triggers': {
    'click @ui.remove': 'remove',
  },
  'events':   {
    'click @ui.edit'(e) {
      e.preventDefault();
      if (this.state === 'edit') {
        this.$('input').focus();
        return;
      }
      this.state = 'edit';
      this.render();
    },
  },
  'serializeData'() {
    var data = Marionette.ItemView.prototype.serializeData.apply(this, arguments);
    data.state = this.state;
    data.lcl = this.model.get('translations').indexOfLocale(this.currentLocale);

    return data;
  },
  'changeLocale'(locale) {
    this.currentLocale = locale;
    this.render();
  },
});
