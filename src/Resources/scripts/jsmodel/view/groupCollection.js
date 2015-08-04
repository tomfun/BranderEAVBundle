define([
    './baseCollection',
    'underscore',

    'backbone.radio',
], function (Base, _) {
    'use strict';

    var BaseProto = Base.prototype;

    return Base.extend({
        templateName:       '@BranderEAV/Widgets/group.collection.twig',
        childViewContainer: 'ul',
        initialize:         function (options) {
            BaseProto.initialize.call(this, _.extend(options, {channel: "attribute-group"}));
        },

        events: {
            "click .add-new-model": function () {
                this.channel.trigger('select', new this.collection.model());
            }
        }
    });
});