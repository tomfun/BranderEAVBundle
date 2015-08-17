define([
    'lodash',
    'brander-elastica-skeleton/listing/abstractListing',
    'util/basemodel',
    'backbone',

    'brander-eav/listing/filterModel',
    'brander-eav/listing/filterCollection',
], function (_, abstractListing, BaseModel, Backbone,
             FrontFilterModel, FrontFilterCollection) {
    'use strict';

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
    var factory = function (Filter, types, RowModel, RowCollection, ResultModel, disableCategoryFilters) {
        if (!ResultModel) {
            ResultModel = BaseModel;
        }
        if (!_.isFunction(ResultModel.extend)) {
            throw "wrong model";
        }

        var relationsExtend = ResultModel.prototype.relations ? ResultModel.prototype.relations : [],
            relations       = [
                {
                    "type":           Backbone.HasMany,
                    "key":            'rows',
                    "relatedModel":   RowModel,
                    "collectionType": RowCollection,
                },
                {
                    "type":           Backbone.HasMany,
                    "key":            'filterableAttributes',
                    "relatedModel":   FrontFilterModel,
                    "collectionType": FrontFilterCollection,
                },
            ],
            relationHash    = {},
            convertToHash   = function (array) {
                _.each(array, function (hash) {
                    relationHash[hash.key] = hash;
                });
            },
            convertToArray  = function (hash) {
                var array = [];
                _.each(hash, function (hash) {
                    array.push(hash);
                });
                return array;
            };
        convertToHash(relations);
        convertToHash(relationsExtend);

        var ResultModelExtended = ResultModel.extend({
                "relations": convertToArray(relationHash)//process old and new relations. new can override old.
            }),
            /**
             * Listing model with filter model and filteredResult model within
             */
            Model               = abstractListing(Filter, types, ResultModelExtended, disableCategoryFilters);

        return Model.extend({
            getAvailableResultTypes: function () {
                return types;
            },
        });
    };
    return factory;
});