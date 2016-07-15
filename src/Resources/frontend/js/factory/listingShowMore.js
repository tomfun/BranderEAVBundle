import _ from 'lodash';
import abstractListing from './listing';
import BaseModel from 'brander-eav/basemodel';


/**
 * Set relation at ResultModel, add patch to it, add method at ListingModel. Proxy to elastica skeleton factory
 *
 * @param Filter
 * @param types
 * @param RowModel
 * @param RowCollection
 * @param ResultModel
 * @param disableCategoryFilters
 * @returns {*}
 */
var factory = function (Filter, types, RowModel, RowCollection, ResultModel, disableCategoryFilters) {
  if (!ResultModel) {
    ResultModel = BaseModel;
  }
  if (!_.isFunction(ResultModel.extend)) {
    throw 'wrong model';
  }
  RowCollection = RowCollection.extend({
    'comparator': 'explicitOrder',
  });
  var FilterModel = Filter.extend({
    'ignorePageAttributes': _.without(Filter.prototype.ignorePageAttributes, 'order'), //! showMore когда меняется сортировка, то тоже должна сбрасыватьс страница
  });

  var ResultModelExtended = ResultModel.extend({
      'pageSize': 10,
        // Data from server can be parsed in wrong sort order. Происходят проблемы сортировки из-за шоу мор
      'parse'(data) {
        var order = this.pageSize * (data.page - 1);
        _.each(data.rows, function (v) {
          v.explicitOrder = order++;
        });
        return data;
      },

        // How much items left
      'calculateShowMore'() {
        var moreNumber = this.get('countTotal') - this.get('page') * this.pageSize;
        moreNumber = moreNumber > this.pageSize ? this.pageSize : moreNumber;
        moreNumber = moreNumber >= 0 ? moreNumber : 0;
        return moreNumber;
      },
    }),

      /**
       * Listing model with filter model and filteredResult model within
       */
    Model               = abstractListing(FilterModel, types, RowModel, RowCollection, ResultModelExtended, disableCategoryFilters);

  return Model.extend({
    fetchOnChange(filterModel) {
      var changed = filterModel.changedAttributes();
      if (changed && changed.hasOwnProperty('page') && _.keys(changed).length === 1 && changed.page > 1) {
        this.fetchDelayed({fetchOnChange: true, remove: false});
      } else {
        this.fetchDelayed({fetchOnChange: true});
      }
    },
    showMore(currentResultType) {
      var filter = this.get('filter');
      if (currentResultType === undefined) {
        currentResultType = filter.getCurrentType();
      }
      var result = this.get(currentResultType),
        pageR  = result.get('page'),
        pageF  = filter.get('page');
      filter.set('page', (pageR > pageF ? pageF : pageR) + 1);
      this.fetchDelayed({remove: false, showMore: true});
    },
    overallCount() {
      var count = 0;
      _.each(this.getAvailableResultTypes(), function (key) {
        count += this.get(key).get('countTotal');
      }, this);
      return count;
    },
  });
};
export default factory;
