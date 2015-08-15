define([
    './abstractFilterView',
    'lodash',
    'jquery'
], function (BaseView, _, $) {
    'use strict';

    return BaseView.extend({
        "templateName": '@BranderEAV/Filters/simpleRangeFilterView.twig',
        "unit":         "km",
        "initialize":   function (options) {
            BaseView.prototype.initialize.apply(this, arguments);
            this.filter = options.filter;
            _.extend(this, _.pick(options,
                ['filter', 'lt', 'lte', 'gt', 'gte', 'min', 'max', 'lat', 'lon', 'distance', 'discriminator', 'unit']
            ));

            if (options.model && options.model.get('attribute')) {
                var descr = options.model.get('attribute').get('discr');
                this.discriminator = descr;
            }
            this.on('render', function () {
                var data,
                    field = this.model.get('field').join('.'),
                    tmp   = this.filter.get(field);
                switch (this.discriminator) {
                    case 'numeric':
                    case 'date':
                        data = _.pick(this, ['lt', 'lte', 'gt', 'gte']);
                        if (tmp) {
                            data = _.extend(data, this.getRangeData(tmp));
                        }
                        break;
                    case 'location':
                        data = _.pick(this, ['lat', 'lon', 'distance', 'unit']);
                        if (tmp) {
                            data = _.extend(data, this.getGeoFilterData(tmp));
                        }
                        break;
                }
                _.each(data, function (val, key) {
                    this.$('[name=' + key + ']').val(val);
                }, this);
            }, this);
        },

        "events": {
            "change [name]": "dataConverter"
        },

        "dataConverter": function (event) {
            var target = $(event.currentTarget);
            if (['lt', 'lte', 'gt', 'gte', 'lat', 'lon', 'distance'].indexOf(target.prop('name')) === -1) {
                return;
            }
            var val   = target.val(),
                type  = target.prop('name'),
                field = this.model.get('field').join('.');
            switch (this.discriminator) {
                case 'select':
                    console.error('wrong filter type');
                    break;
                case 'input':
                case 'boolean':
                case 'textarea':
                    throw 'wrong filter type';
                case 'numeric':
                    val = parseFloat(val);
                case 'date':
                    this[type] = val;
                    if (['lt', 'lte', 'gt', 'gte'].indexOf(target.prop('name')) === -1) {
                        return;
                    }
                    _.each(['lt', 'lte', 'gt', 'gte'], function (index) {
                        if (
                            this[index] !== undefined
                            && (_.isNaN(this[index]) || (_.isString(this[index]) && this[index].trim() === ''))
                        ) {
                            this[index] = undefined;
                        }
                    }, this);
                    try {
                        this.filter.set(field, this.getRangeQuery(this.lt, this.gt, this.lte, this.gte));
                    } catch (e) {
                        this.drawError(e);
                    }
                    break;
                case 'location':
                    if (['lat', 'lon', 'distance', 'unit'].indexOf(target.prop('name')) === -1) {
                        return;
                    }
                    this[type] = parseFloat(val);
                    try {
                        this.filter.set(field, this.getGeoPointFilter(this.lat, this.lon, this.distance, this.unit));
                    } catch (e) {
                        this.drawError(e);
                    }
                    break;
            }
            this.drawError('');
        },
        "drawError":     function (e) {
            this.$('.error').text(e);
        },
        "serializeData": function () {
            return _.merge(this.model.toJSON(), {discriminator: this.discriminator, unit: this.unit});
        }
    });
});