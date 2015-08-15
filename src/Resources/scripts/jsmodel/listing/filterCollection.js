define([
    'backbone',
    './filterModel',
], function (Backbone, FilterModel) {
    'use strict';

    var Model = Backbone.Collection.extend({
        "model":      FilterModel,
        "comparator": "viewOrder"
    });

    return Model;
});