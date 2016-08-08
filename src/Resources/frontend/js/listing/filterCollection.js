import Backbone from 'backbone';
import FilterModel from './filterModel';


const Model = Backbone.Collection.extend({
  'model':      FilterModel,
  'comparator': 'viewOrder',
});

export default Model;
