define([
    'lodash',
    'backbone',
    'router',
    'util/basemodel',
    './attribute',
    './attributeCollection'
], function (_, Backbone, Routing, BaseModel, AttributeModel, AttributeCollection) {
    'use strict';

    var Model = BaseModel.extend({
        "relations": [
            {
                "type":           Backbone.HasMany,
                "key":            'attributes',
                "relatedModel":   AttributeModel,
                "collectionType": AttributeCollection,
            }
        ],

        "url": function () {
            return this.isNew()
                ? Routing.generate('brander_eav_attribute_set_post')
                : Routing.generate('brander_eav_attribute_set_get', {'attributeSet': this.id});
        },
    });

    return Model;
});