import _ from 'lodash';
import containerTemplate from 'templates/brander-eav/Filters/container.twig';
import Marionette from 'backbone.marionette';
import ChildViews from 'brander-eav/listing/filtersLoader!';


export default Marionette.CompositeView.extend({
  template:             containerTemplate,
  'childViewContainer': 'fieldset',

  'getChildView'(model) {
    return ChildViews[model.get('view')];
  },

  'childViewOptions'(model) {
    return _.merge(model.get('viewOptions'), {
      filter:       this.listing.get('filter'),
      aggregations: this.aggregations,
    });
  },

  'redrawOnChange'() {
    this.listing.get('filters').on('sync', this.render, this);
  },


  'setAggregations'(aggregations) {
    var need;
    _.each(this.children._views, function (view, id) {
      if (view.onChangeAggregations(aggregations)) {
        need = true;
      }
    });
    if (need) {
      this.render();
    }
  },

  'getCollection'() {
    return this.listing.get('filters').get('filterableAttributes');
  },

  'initialize'(options) {
    this.listing = options.listing;
    this.aggregations = options.aggregations;
    this.collection = this.getCollection();
    this.redrawOnChange();
    if (!this.collection.length) {
      this.listing.get('filter').updateFilters();
    }
    Marionette.CompositeView.prototype.initialize.apply(this, arguments);
    if (options && options.templateName) {
      this.templateName = options.templateName;
    }
    if (options && options.childViewContainer) {
      this.childViewContainer = options.childViewContainer;
    }
  },

  'filter'(model) {
    return model.get('isFilterable');
  },
});
