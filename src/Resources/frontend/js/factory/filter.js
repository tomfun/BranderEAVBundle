import _ from 'lodash';
import filterFactory from 'brander-elastica-skeleton/listing/abstractFilter';
import Backbone from 'backbone';
import AttributesModel from './attributeModel';

/**
 * Create Filter Model with eav attribute relation
 * @param routes
 * @param showMore - if true, you must implement getCurrentType method which return string - field in listing model
 * @returns {*}
 */
var factory = function (routes, showMore) {
  showMore = !!showMore;
  var Base = filterFactory(routes);// pure filter model

  var extendHash = {
    'updateFilterAttributes': [], // example: ['category', 'direction'] - when this field changed -> call update filters
    'relations':              [
      {
        'type':         Backbone.HasOne,
        'key':          'attributes',
        'relatedModel': AttributesModel,
      },
    ],
    'updateFilters':          undefined, // will be the method
    'initialize'() {
      Base.prototype.initialize.apply(this, arguments);
      this.set({
        'attributes': new AttributesModel(),
      });
      this.get('attributes').on('change', function () {
        var lst = this.get('attributes').changedAttributes();
        if (lst) { // process eav filters
          // like resetPageHandler
          var changed = _.keys(lst),
            list    = _.intersection(this.ignorePageAttributes, changed);
          if (list.length <= 0 && changed.length > 0) {
            this.set({page: this.defaults.page}, {silent: true});
          }
          var attrs = this.get('attributes').attributes;
          _.each(attrs, function (v, i) {
            if (v === '') {
              delete attrs[i];
            }
          });
        }
        this.trigger('change', this);
      }, this);
      var that          = this,
        updateFilters = this.updateFilters = function () {
          that.trigger('update-filters');
        };
      _.each(_.result(this, 'updateFilterAttributes'), function (fieldName) {
        this.on('change:' + fieldName, updateFilters);
      }, this);
    },

    // clean up
    'toJSON'(category) {
      var ret = Base.prototype.toJSON.apply(this, arguments);

      if (ret.attributes && !Object.keys(ret.attributes).length) {
        delete ret.attributes;
      }

      return ret;
    },

    // is this filter equal to 'val'
    'attrSelected'(attr, val) {
      var a = this.get('attributes') ? this.get('attributes')[attr.id] : null;
      if (!a) {
        return false;
      }
      if (typeof val !== 'undefined') {
        return String(a) === String(val);
      }
      return true;
    },

    'routingSet'(attributes) {
      this.set(attributes, false);
      return this;
    },
  };
  if (showMore) {
    extendHash.needFetchByType = function (type) {
      return this.get('page') <= 1 || this.getCurrentType() === type;
    };
    extendHash.getCurrentType = 'you must override me';
  }

  return Base.extend(extendHash);
};
export default factory;
