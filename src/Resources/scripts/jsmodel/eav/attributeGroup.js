define([
    'lodash',
    'backbone',
    'util/basemodel',
    './attribute',
    './attributeCollection',
    'routing',
    './translationCollection',

    'backbone.chaining'
], function (_, Backbone, BaseModel, AttributeModel, AttributeCollection, Routing, TranslationCollection) {
    'use strict';

    var Model = BaseModel.extend({
        "url": function () {
            return this.isNew()
                ? Routing.generate('brander_eav_attribute_group_post')
                : Routing.generate('brander_eav_attribute_group_get', {'attributeGroup': this.id});
        },

        "relations": [
            {
                "type":           Backbone.HasMany,
                "key":            'attributes',
                "relatedModel":   AttributeModel,
                "collectionType": AttributeCollection,
            },
            {
                "type":           Backbone.HasMany,
                "key":            'translations',
                "relatedModel":   TranslationCollection.Model,
                "collectionType": TranslationCollection,
            }
        ],

        getIds: function() {
            var hash = {};
            _.each(this.get('attributes[*].id'), function (val) {
                hash[String(val)] = true;
            });
            return hash;
        },

        filterValues: function (values) {
            var ids = {},
                res = [];
            if (values instanceof Backbone.Collection) {
                ids = this.getIds();
                values.each(function(val, i) {
                    var attribute;
                    if (val && (attribute = val.get('attribute'))) {
                        if (attribute instanceof AttributeModel && ids[String(attribute.id)]) {
                            res.push(val);
                        }
                    }
                });
                return res;
            //} else {
            //    ids = this.getIds();
            //    if (_.isArray(values)) {
            //
            //    }
            }
            throw "not a collection";
        }
    });

    return Model;
});