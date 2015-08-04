define([
    './baseCollection',
    'underscore',

    'backbone.radio',
    'jquery-ui',
], function (Base, _) {
    'use strict';

    var BaseProto = Base.prototype;

    return Base.extend({
        templateName:       '@BranderEAV/Widgets/attribute.collection.twig',
        childViewContainer: 'ul',
        draggable:  true,
        initialize: function (options) {
            BaseProto.initialize.call(this, _.extend({channel: "attribute", draggable: true}, options));
        }
    });
});