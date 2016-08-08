import _ from 'lodash';
import $ from 'jquery';
import BaseView from './abstractFilterView';
import Moment from 'moment';
import __ from 'underscore.string';


export default BaseView.extend({
  templateName:    '@BranderEAV/Filters/bucketRangeFilterView.twig',
  'unit':          'грн',
  'discriminator': 'numeric',
  'autoApply':     false,
  'slowClose':     1200,

  'initialize'(options) {
    BaseView.prototype.initialize.apply(this, arguments);
    if (options.model && options.model.get('attribute')) {
      let descr = options.model.get('attribute').get('discr');
      this.discriminator = descr;
      if (descr === 'date') {
        this.unit = '';// formatViewDate
      }
    }
    _.extend(this, _.pick(options,
      ['filter', 'lt', 'lte', 'gt', 'gte', 'discriminator', 'unit', 'title', 'autoApply', 'slowClose']
    ));
    this.slowClose = this.slowClose === false ? false : parseInt(this.slowClose);

    this.on('render', function () {
      let data,
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
      }
      _.each(data, function (val, key) {
        this.$('[name=' + key + ']').val(val);
      }, this);
      this.updateElements();
    }, this);
  },

  'updateElements'() {
    let that = this;
    this.$('.bucket-range').each(function (i, el) {
      let it  = $(el),
        lt  = it.data('lt'),
        gte = it.data('gte')/* ,
           /*less = _.findWhere(that.aggregations, {'key_value': lt}),
           great = _.findWhere(that.aggregations, {'key_value': gte};
      if (!lt) {
        lt = undefined;
      }
      if (!gte) {
        gte = undefined;
      }
      if (lt === that.lt && that.gte === gte) {
        it.addClass('active');
      } else {
        it.removeClass('active');
      }
    });
    this.$('.slide-holder').hide();
  },

  'events': {
    'change [name]':       'dataConverter',
    'click .opener':       'toggleOpener',
    'click .bucket-range': 'setRangeClick',
    'click a.apply':       'applyClick',
  },

  'setRangeClick'(e) {
    let it = $(e.currentTarget);
    this.lt = it.data('lt');
    this.gte = it.data('gte');
    this.applyClick(e);
    it.addClass('active').css('opacity', '0.7');
  },

  'applyClick'(e) {
    if (e) {
      e.preventDefault();
    }
    let field = this.model.get('field').join('.');
    try {
      this.filter.set(field, this.getRangeQuery(this.lt, this.gt, this.lte, this.gte));
      this.drawError('');
    } catch (e) {
      this.drawError(e);
    }
    if (this.slowClose === false) {
      return;
    }
    this.$('.opener').removeClass('active');
    this.$('.slide-holder').slideUp(this.slowClose);
  },

  'toggleOpener'(e) {
    e.preventDefault();
    $(e.currentTarget).toggleClass('active');
    this.$('.slide-holder').slideToggle();
  },

  'dataConverter'(event) {
    let target = $(event.currentTarget);
    if (['lt', 'lte', 'gt', 'gte', 'aggregations'].indexOf(target.prop('name')) === -1) {
      return;
    }
    let val   = target.val(),
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
        _.each(['lt', 'lte', 'gt', 'gte'], function (index) {
          if (
            this[index] !== undefined
            && (_.isNaN(this[index]) || (_.isString(this[index]) && this[index].trim() === ''))
          ) {
            this[index] = undefined;
          }
        }, this);
        if (!this.autoApply) {
          return;
        }
        try {
          this.filter.set(field, this.getRangeQuery(this.lt, this.gt, this.lte, this.gte));
        } catch (e) {
          this.drawError(e);
          return;
        }
        break;
    }
    this.drawError('');
  },
  'drawError'(e) {
    this.$('.error').text(e);
  },
  'serializeData'() {
    return _.merge(this.model.toJSON(), {
      discriminator: this.discriminator,
      unit:          this.unitFormat.bind(this),
      aggregations:  this.aggregations,
      title:         this.title,
    });
  },

  'unitFormat'(key, view_key) {
    return this.unit;
  },

  'formatViewNumber'(number) {
    return __.numberFormat(number, Math.abs(number) > 1 ? 0 : 3, '.', ' ');
  },

  'formatViewDate'(momentObj) {
    return momentObj.format('L');
  },

  'onChangeAggregations'(aggregations) {
    let aggName = this.model.get('field').join('.');
    let success = function () {
      if (this.discriminator === 'date') {
        _.each(this.aggregations, function (v, i) {
          let moment = Moment.unix(v.key / 1000);
          v.key_value = moment.format();
          v.key_view = this.formatViewDate(moment);
        }, this);
      } else {
        _.each(this.aggregations, function (v, i) {
          v.key_value = v.key;
          v.key_view = this.formatViewNumber(v.key);
        }, this);
      }
      this.render();
    }.bind(this);
    if (aggName && aggregations[aggName]) {
      this.aggregations = aggregations[aggName].range_basket;
      success();
    } else {
      if (this.aggregations && this.aggregations.length) {
        this.aggregations = [];
        success();
        // return true;
      }
    }
    return false;
  },
});
