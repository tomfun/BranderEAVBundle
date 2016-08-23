import BaseView from './abstractFilterView';
import Backbone from 'backbone';
import _ from 'lodash';
import 'backbone.modelbinder';
import {render as templateInput} from 'templates/brander-eav/Filters/input.twig';
import {render as templateSelect} from 'templates/brander-eav/Filters/select.twig';
import {render as templateBoolean} from 'templates/brander-eav/Filters/bool.twig';


export default BaseView.extend({
  'initialize'(options) {
    BaseView.prototype.initialize.apply(this, arguments);
    this.filter = options.filter;
    if (options.model && options.model.get('attribute')) {
      const descr = options.model.get('attribute').get('discr');
      this.descriminator = descr;
      switch (descr) {
        case 'select':
          this.template = templateSelect;
          break;
        case 'input':
        case 'textarea':
          this.template = templateInput;
          break;
        case 'boolean':
          this.template = templateBoolean;
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
        default:
          throw new Error(`There is no any acceptable template for this attribute type: ${descr}`);
      }
    }
    this.modelBinder = new Backbone.ModelBinder();
    this.on('render', () => {
      const bindings = Backbone.ModelBinder.createDefaultBindings(this.el, 'name');
      _.each(bindings, (v) => v.converter = this.dataConverter);
      this.modelBinder.bind(this.filter, this.el, bindings);
    });
  },

  'dataConverter'(dir, val) {
    return val;
  },

});
