define([
    'lodash',
    'backbone',
    './attribute',
    'router'
], function (_, Backbone, Model, Routing) {
    'use strict';

    var Collection = Backbone.Collection.extend({
        "model": Backbone.Model,
        "parse": function(v) {
            return _.map(v, function(v, i) {
                return {
                    "descr": i,
                    "class": v
                };
            });
        },
        "url": function() {
            return Routing.generate('brander_eav_attribute_type_list');
        }
    });

    return Collection;
});