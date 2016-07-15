import _ from 'lodash';
import Backbone from 'backbone';
import Routing from 'router';


var Collection = Backbone.Collection.extend({
  'model': Backbone.Model,
  'parse'(v) {
    return _.map(v, function (v, i) {
      return {
        'descr': i,
        'class': v,
      };
    });
  },
  'url'() {
    return Routing.generate('brander_eav_attribute_type_list');
  },
});

export default Collection;
