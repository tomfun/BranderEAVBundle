import _ from 'lodash';
import Backbone from 'backbone';
import Model from './attributeGroup';
import Routing from 'router';


var Collection = Backbone.Collection.extend({
  'fetchOptions': {},

  'initialize'(options) {
    Backbone.Collection.prototype.initialize.apply(this, arguments);
    this.fetchOptions = {};
    this.fetchOptions.manage = options && options.manage;
  },

  'url'() {
    return Routing.generate('brander_eav_group_list', {manage: this.fetchOptions.manage ? 'true' : false});
  },

  'model': Model,

  groupValues(values, returnNotInGroups, excludeEmpty) {
    if (returnNotInGroups === undefined) {
      returnNotInGroups = true;
    }
    if (excludeEmpty === undefined) {
      excludeEmpty = true;
    }
    var overall = [],
      result  = {};
    this.each(function (group, i) {
      var outValues = group.filterValues(values);
      if (excludeEmpty && !outValues.length) {
        return;
      }
      result[group.id] = {
        group,
        values: outValues,
      };
      overall = _.union(overall, outValues);
    });
    if (returnNotInGroups) {
      var tmpVals   = _.isArray(values) ? values : values.toArray(),
        outValues = _.difference(_.uniq(tmpVals), overall);
      if (outValues.length) {
        result.rest = {
          group:  'rest',
          values: outValues,
        };
      }
    }
    return result;
  },
});

export default Collection;
