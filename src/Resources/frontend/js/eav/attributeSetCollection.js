import Backbone from 'backbone';
import Model from './attributeSet';
import Routing from 'router';


const Collection = Backbone.Collection.extend({
  'fetchOptions': {},
  'initialize'(options) {
    Backbone.Collection.prototype.initialize.apply(this, arguments);
    this.fetchOptions = {};
    this.fetchOptions.manage = options && options.manage;
  },
  'url'() {
    // cache only
    return Routing.generate('brander_eav_set_list', {manage: this.fetchOptions.manage ? 'true' : false});
  },
  'model':        Model,

});

export default Collection;
