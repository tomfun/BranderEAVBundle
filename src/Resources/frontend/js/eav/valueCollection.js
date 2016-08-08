import Backbone from 'backbone';
import Model from './value';


const Collection = Backbone.Collection.extend({
  'model': Model,
});

export default Collection;
