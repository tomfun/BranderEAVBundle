import 'backbone-chaining'; // for special model binder's names
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
const factory = function (routes, showMore) {
  showMore = !!showMore;
  const Base = filterFactory(routes);// pure filter model

  const extendHash = {
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
        const lst = this.get('attributes').changedAttributes();
        if (lst) { // process eav filters
          // like resetPageHandler
          let changed = _.keys(lst),
            list    = _.intersection(this.ignorePageAttributes, changed);
          if (list.length <= 0 && changed.length > 0) {
            this.set({page: this.defaults.page}, {silent: true});
          }
          const attrs = this.get('attributes').attributes;
          _.each(attrs, function (v, i) {
            if (v === '') {
              delete attrs[i];
            }
          });
        }
        this.trigger('change', this);
      }, this);
      let that          = this,
        updateFilters = this.updateFilters = function () {
          that.trigger('update-filters');
        };
      _.each(_.result(this, 'updateFilterAttributes'), function (fieldName) {
        this.on('change:' + fieldName, updateFilters);
      }, this);
    },

    // clean up
    'toJSON'(category) {
      const ret = Base.prototype.toJSON.apply(this, arguments);

      if (ret.attributes && !Object.keys(ret.attributes).length) {
        delete ret.attributes;
      }

      return ret;
    },

    set(path, value, ...args) {
      const splitPath = _.split(path, '.');
      if (splitPath[0] === 'attributes') {
        let attrs = this.get('attributes');
        if (attrs === null) {
          attrs = new AttributesModel();
        }
        attrs.set(splitPath[1], value);
        AttributesModel.prototype.set.call(this, splitPath[0], attrs);
      } else {
        AttributesModel.prototype.set.call(this, path, value, ...args);
      }
    },

    // is this filter equal to 'val'
    'attrSelected'(attr, val) {
      const a = this.get('attributes') ? this.get('attributes')[attr.id] : null;
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
