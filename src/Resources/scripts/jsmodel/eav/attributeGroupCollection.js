define([
    'lodash',
    'backbone',
    './attributeGroup',
    'router'
], function (_, Backbone, Model, Routing) {
    'use strict';

    var Collection = Backbone.Collection.extend({
        "fetchOptions": {},

        "initialize":   function (options) {
            Backbone.Collection.prototype.initialize.apply(this, arguments);
            this.fetchOptions = {};
            this.fetchOptions.manage = options && options.manage;
        },

        "url": function () {
            return Routing.generate('brander_eav_group_list', {manage: this.fetchOptions.manage ? 'true' : false});
        },

        "model": Model,

        groupValues: function (values, returnNotInGroups, excludeEmpty) {
            if (returnNotInGroups === undefined) {
                returnNotInGroups = true;
            }
            if (excludeEmpty === undefined) {
                excludeEmpty = true;
            }
            var overall = [],
                result = {};
            this.each(function (group, i) {
                var outValues = group.filterValues(values);
                if (excludeEmpty && !outValues.length) {
                    return;
                }
                result[group.id] = {
                    group: group,
                    values: outValues
                };
                overall = _.union(overall, outValues);
            });
            if (returnNotInGroups) {
                var tmpVals = _.isArray(values) ? values : values.toArray(),
                    outValues = _.difference(_.uniq(tmpVals), overall);
                if (outValues.length) {
                    result.rest = {
                        group: 'rest',
                        values: outValues
                    };
                }
            }
            return result;
        }
    });

    return Collection;
});