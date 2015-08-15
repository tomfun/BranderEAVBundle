define([
    'util/basemodel',
    'backbone',
    'brander-eav/eav/attribute',

    'backbone.relational',
], function (BaseModel, Backbone, AttributeModel) {
    'use strict';

    var Model = BaseModel.extend({
        "defaults":  {
            "field":        [],
            "isFilterable": false,
            "isSortable":   false,
            "view":         "brander-eav/listing/simpleFilterView",
            "viewOptions":  {},
            "viewOrder":    0,
        },
        "relations": [
            {
                "type":         Backbone.HasOne,
                "key":          'attribute',
                "relatedModel": AttributeModel
            }
        ],
    });

    return Model;
});