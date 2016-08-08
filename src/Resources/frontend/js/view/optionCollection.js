import Base from './baseCollection';
import Collection from 'brander-eav/eav/optionCollection';
import {render as template} from 'templates/brander-eav/Widgets/option.collection.twig';
import 'backbone.radio';


const BaseProto = Base.prototype;

export default Base.extend({
  template,

  options:            {
    childViewOptions: {},
  },
  childViewContainer: 'ul',
  initialize(options) {
    BaseProto.initialize.call(this, arguments);
    this.currentLocale = options.currentLocale;
    this.options.childViewOptions.currentLocale = this.currentLocale;
    this.on('childview:remove', function (childView, hash) {
      const model = hash.model;
      this.removeModel(model);
    }, this);
  },

  changeLocale(locale) {
    if (locale === this.currentLocale) {
      return;
    }
    this.currentLocale = locale;
    this.options.childViewOptions.currentLocale = this.currentLocale;
    this.children.each(function (v) {
      v.changeLocale(locale);
    });
  },

  events: {
    'click .add-option'(e) {
      e.preventDefault();
      const model = new Collection.Model();
      this.collection.add(model);
      const itemView = this.children.findByModel(model);
      itemView.trigger('processing');
    },
  },

  removeModel(model) {
    this.collection.remove(model);
  },
});

