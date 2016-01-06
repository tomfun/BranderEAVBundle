define([
    'lodash',
    'jquery',
    'backbone',
    'util/basemodel',
    'router',
    './optionCollection',
    './translationCollection'
], function (_, $, Backbone, BaseModel, Routing, OptionCollection, TranslationCollection) {
    'use strict';

    var Model = BaseModel.extend({
        "url": function () {
            return this.isNew()
                ? Routing.generate('brander_eav_attribute_post')
                : Routing.generate('brander_eav_attribute_get', {'attribute': this.id});
        },

        "check": function () {
            if (this.isNew()) {
                return $.Deferred().resolve();
            }
            var url = Routing.generate('brander_eav_attribute_check', {'attribute': this.id});
            return $.ajax({
                "method":      "patch",
                "dataType":    "json",
                "processData": false,
                "url":         url,
                "data":        JSON.stringify(this.toJSON())
            });
        },

        "hasOptions": function () {
            return this.get('discr') === 'select';
        },

        "relations": [
            {
                "type":           Backbone.HasMany,
                "key":            'options',
                "relatedModel":   OptionCollection.Model,
                "collectionType": OptionCollection,
            },
            {
                "type":           Backbone.HasMany,
                "key":            'translations',
                "relatedModel":   TranslationCollection.Model,
                "collectionType": TranslationCollection,
            }
        ],

        "defaults": {
            "title":        "New attribute",
            "isRequired":   false,
            "isFilterable": false,
            "isSortable":   false,
            "discr":        "input"
        }
    });

    return Model;
});