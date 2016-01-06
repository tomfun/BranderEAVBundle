define([
    'lodash',
    'backbone',
    './attributeSet',
    'router'
], function (_, Backbone, Model, Routing) {
    'use strict';

    var Collection = Backbone.Collection.extend({
        "fetchOptions": {},
        "initialize":   function (options) {
            Backbone.Collection.prototype.initialize.apply(this, arguments);
            this.fetchOptions = {};
            this.fetchOptions.manage = options && options.manage;
        },
        "url":          function () {
            //cache only
            return Routing.generate('brander_eav_set_list', {manage: this.fetchOptions.manage ? 'true' : false});
        },
        "model": Model

    });

    return Collection;
});