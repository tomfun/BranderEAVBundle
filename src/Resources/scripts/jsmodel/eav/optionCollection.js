define([
    'lodash',
    'backbone',
    'util/basemodel',
    './translationCollection'
], function (_, Backbone, BaseModel, TranslationCollection) {
    'use strict';

    var Model = BaseModel.extend({
            "defaults": {
                title: "new option"
            },
            "relations": [
                {
                    "type":           Backbone.HasMany,
                    "key":            'translations',
                    "relatedModel":   TranslationCollection.Model,
                    "collectionType": TranslationCollection,
                }
            ],
        }),
    Collection = Backbone.Collection.extend({
        "model": Model,
    });

    Collection.Model = Model;

    return Collection;
});