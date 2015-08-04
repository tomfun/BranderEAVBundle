define([
    'lodash',
    'backbone',
    'util/basemodel',
    './attribute'
], function (_, Backbone, BaseModel, AttributeModel) {
    'use strict';

    var Model = BaseModel.extend({
        "defaults":   {
            "value": null,
            "discr": null,
        },
        "initialize": function () {
            var attribute = this.get('attribute');
            if (attribute) {
                this.set('discr', attribute.get('discr'));

                //TODO: kostil
                if (attribute.get('discr') === 'boolean' && !this.get('value')) {
                    this.set('value', false);
                }
            }
        },
        "relations":  [
            {
                "type":         Backbone.HasOne,
                "key":          'attribute',
                "relatedModel": AttributeModel
            }
        ],
    });

    return Model;
});