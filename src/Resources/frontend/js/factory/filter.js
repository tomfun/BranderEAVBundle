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
    updateFilters:          undefined, // will be the method
    pageResetListener() {
      const lst = this.changedAttributes();
      if (lst) { // process eav filters
        // like resetPageHandler
        const changed = _.keys(lst);
        const list    = _.intersection(this.ignorePageAttributes, changed);
        if (list.length <= 0 && changed.length > 0 /*&& !changed.indexOf('page')*/) {
          this.set({page: this.defaults.page}, {silent: true});
        }
        const attrs = this.get('attributes') && this.get('attributes').attributes;
        _.each(attrs, (v, i) => {
          if (v === '') {
            delete attrs[i];
          }
        });
        // this.trigger('change', this);
      }
    },
    initialize(...args) {
      Base.prototype.initialize.apply(this, args);
      this.on('change', this.pageResetListener);
      this.on('change:attributes', () => {
        if (this._knownAttributes) {
          this.stopListening(this._knownAttributes);
        }
        const attrs = this.get('attributes');
        if (!attrs) {
          return;
        }
        this._knownAttributes = attrs;
        this.listenTo(this._knownAttributes, 'change', () => {
          this.set({page: this.defaults.page});
          this.trigger('change', this);
        });
      });
      this.set({
        attributes: new AttributesModel(),
      });
      const updateFilters = this.updateFilters = () => this.trigger('update-filters');
      _.each(_.result(this, 'updateFilterAttributes'), (fieldName) => {
        this.on(`change:${fieldName}`, updateFilters);
      });
    },

    // clean up
    toJSON(category) {
      const ret = Base.prototype.toJSON.apply(this, arguments);

      if (ret.attributes && !Object.keys(ret.attributes).length) {
        delete ret.attributes;
      }

      return ret;
    },

    set(pathOrData, ...args) {
      let data;
      let otherArgs;
      if (!_.isObject(pathOrData)) {
        data = {[pathOrData]: args[0]};
        otherArgs = args.slice(1);
      } else {
        data = pathOrData;
        otherArgs = args;
      }
      const specialKeys = _.keys(data).filter((k) => {
        const pathArr = _.split(k, '.', 2);
        return pathArr.length === 2 && pathArr[0] === 'attributes';
      });
      Base.prototype.set.call(this, _.omit(data, specialKeys), ...otherArgs);
      _.each(_.pick(data, specialKeys), (value, path) => {
        const splitPath = _.split(path, '.', 2);
        let attrs = this.get('attributes');
        if (attrs === null) {
          attrs = new AttributesModel();
          Base.prototype.set.call(this, 'attributes', attrs);
        }
        attrs.set(splitPath[1], value);
      });
    },

    // is this filter equal to 'val'
    attrSelected(attr, val) {
      const a = this.get('attributes') ? this.get('attributes')[attr.id] : null;
      if (!a) {
        return false;
      }
      if (typeof val !== 'undefined') {
        return String(a) === String(val);
      }
      return true;
    },

    routingSet(attributes) {
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
