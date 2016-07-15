import BaseView from './abstractFilterView';
import Backbone from 'backbone';
import _ from 'lodash';
import 'backbone.modelbinder';


export default BaseView.extend({
  templateName: {
    template:          '@BranderEAV/Filters/input.twig',
    'templateSelect':  '@BranderEAV/Filters/select.twig',
    'templateBoolean': '@BranderEAV/Filters/bool.twig',
  },

  'initialize'(options) {
    BaseView.prototype.initialize.apply(this, arguments);
    this.templateInput = this.template;
    this.filter = options.filter;
    if (options.model && options.model.get('attribute')) {
      var descr = options.model.get('attribute').get('discr');
      this.descriminator = descr;
      switch (descr) {
        case 'select':
          this.template = this.templateSelect;
          break;
        case 'input':
        case 'textarea':
          this.template = this.templateInput;
          break;
        case 'boolean':
          this.template = this.templateBoolean;
          break;
        // case 'numeric':
        //    this.template = this.templateNumeric;
        //    break;
        // case 'date':
        //    this.template = this.templateDate;
        //    break;
        // case 'location':
        //    this.template = this.templateLocation;
        //    break;
      }
    }
    this.modelBinder = new Backbone.ModelBinder();
    this.on('render', function () {
      var bindings = Backbone.ModelBinder.createDefaultBindings(this.el, 'name');
      _.each(bindings, function (v) {
        v.converter = this.dataConverter;
      }, this);
      this.modelBinder.bind(this.filter, this.el, bindings);
    }, this);
  },

  'dataConverter'(dir, val) {
    return val;
  },

});
