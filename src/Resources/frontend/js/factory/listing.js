import _ from 'lodash';
import abstractListing from 'brander-elastica-skeleton/listing/abstractListing';
import BaseModel from 'brander-eav/basemodel';
import Backbone from 'backbone';
import FrontFilterModel from 'brander-eav/listing/filterModel';
import FrontFilterCollection from 'brander-eav/listing/filterCollection';


/**
 * Set relation at ResultModel and proxy to elastica skeleton factory
 *
 * @param Filter
 * @param types
 * @param RowModel
 * @param RowCollection
 * @param ResultModel
 * @param disableCategoryFilters
 * @returns {*}
 */
const factory = function (Filter, types, RowModel, RowCollection, ResultModel, disableCategoryFilters) {
  if (!ResultModel) {
    ResultModel = BaseModel;
  }
  if (!_.isFunction(ResultModel.extend)) {
    throw 'wrong model';
  }

  let relationsExtend = ResultModel.prototype.relations ? ResultModel.prototype.relations : [],
    relations       = [
      {
        'type':           Backbone.HasMany,
        'key':            'rows',
        'relatedModel':   RowModel,
        'collectionType': RowCollection,
      },
      {
        'type':           Backbone.HasMany,
        'key':            'filterableAttributes',
        'relatedModel':   FrontFilterModel,
        'collectionType': FrontFilterCollection,
      },
    ],
    relationHash    = {},
    convertToHash   = function (array) {
      _.each(array, function (hash) {
        relationHash[hash.key] = hash;
      });
    },
    convertToArray  = function (hash) {
      const array = [];
      _.each(hash, function (hash) {
        array.push(hash);
      });
      return array;
    };
  convertToHash(relations);
  convertToHash(relationsExtend);

  let ResultModelExtended = ResultModel.extend({
      'relations': convertToArray(relationHash), // process old and new relations. new can override old.
      parse(data) {
        let order = this.pageSize * (data.page - 1);
        _.each(data.rows, function (v) {
          v.explicitOrder = order++;
        });
        return data;
      },
    }),

      /**
       * Listing model with filter model and filteredResult model within
       */
    Model               = abstractListing(Filter, types, ResultModelExtended, disableCategoryFilters);

  return Model.extend({
    getAvailableResultTypes() {
      return types;
    },
  });
};
export default factory;
