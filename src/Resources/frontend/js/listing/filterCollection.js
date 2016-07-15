import Backbone from 'backbone';
import FilterModel from './filterModel';


var Model = Backbone.Collection.extend({
  'model':      FilterModel,
  'comparator': 'viewOrder',
});

export default Model;
