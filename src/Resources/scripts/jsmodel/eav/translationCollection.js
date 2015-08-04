define([
    'lodash',
    'backbone',
    'util/basemodel',
], function (_, Backbone, BaseModel) {
    'use strict';

    var Model      = BaseModel.extend({}),
        Collection = Backbone.Collection.extend({
            "model":       Model,
            indexOfLocale: function (locale, createNew) {
                var index = _.findIndex(this.toArray(), function (model) {
                    return model.get('locale') === locale;
                });
                if (index !== -1) {
                    return index;
                }
                if (createNew === undefined || createNew === true) {
                    this.add(new Model({locale: locale}));
                    return this.indexOfLocale(locale, false);
                }
            }
        });

    Collection.Model = Model;

    return Collection;
});