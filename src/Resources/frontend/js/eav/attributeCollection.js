import Backbone from 'backbone';
import Model from './attribute';
import Routing from 'router';


let BaseProto  = Backbone.Collection.prototype,
  Collection = Backbone.Collection.extend({
    'fetchOptions': {},
    'initialize'(options) {
      BaseProto.initialize.apply(this, arguments);
      this.fetchOptions = {};
      this.fetchOptions.manage = options && options.manage;
    },
    'model':        Model,
    'url'() {
      return Routing.generate('brander_eav_attribute_list', {manage: this.fetchOptions.manage ? 'true' : false});
    },
  });

export default Collection;
