import Backbone from 'backbone';
import Model from './value';


var Collection = Backbone.Collection.extend({
  'model': Model,
});

export default Collection;
