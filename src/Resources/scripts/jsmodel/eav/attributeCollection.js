define([
    'lodash',
    'backbone',
    './attribute',
    'routing'
], function (_, Backbone, Model, Routing) {
    'use strict';

    var BaseProto  = Backbone.Collection.prototype,
        Collection = Backbone.Collection.extend({
            "fetchOptions": {},
            "initialize":   function (options) {
                BaseProto.initialize.apply(this, arguments);
                this.fetchOptions = {};
                this.fetchOptions.manage = options && options.manage;
            },
            "model":        Model,
            "url":          function () {
                return Routing.generate('brander_eav_attribute_list', {manage: this.fetchOptions.manage ? 'true' : false});
            }
        });

    return Collection;
});