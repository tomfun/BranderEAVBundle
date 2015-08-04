define([
    'lodash',
    'backbone',
    './value'
], function (_, Backbone, Model) {
    'use strict';

    var Collection = Backbone.Collection.extend({
        "model": Model
    });

    return Collection;
});