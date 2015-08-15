define([
    'lodash',
    'backbone.marionette',
    'templating',
    'brander-eav/listing/filtersLoader!',
], function (_, Marionette, templating, ChildViews) {
    'use strict';

    return Marionette.CompositeView.extend({
        "templateName":       '@BranderEAV/Filters/container.twig',//'@BranderEAV/Widgets/layout.twig',
        "template":           undefined,
        "childViewContainer": 'fieldset',

        "getChildView": function (model) {
            return ChildViews[model.get('view')];
        },

        "childViewOptions": function (model) {
            return _.merge(model.get('viewOptions'), {
                filter: this.listing.get('filter')
            });
        },

        "redrawOnChange": function () {
            this.listing.get('filters').on('sync', this.render, this);
        },

        "getCollection": function () {
            return this.listing.get('filters').get('filterableAttributes');
        },

        "initialize": function (options) {
            this.listing = options.listing;
            this.collection = this.getCollection();
            this.redrawOnChange();
            if (!this.collection.length) {
                this.listing.get('filter').updateFilters();
            }
            Marionette.CompositeView.prototype.initialize.apply(this, arguments);
            if (options && options.templateName) {
                this.templateName = options.templateName;
            }
            this.template = templating.get(this.templateName);
            if (options && options.childViewContainer) {
                this.childViewContainer = options.childViewContainer;
            }
        },

        "filter": function (model) {
            return model.get('isFilterable');
        }
    });
});