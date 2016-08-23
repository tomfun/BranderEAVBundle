import Marionette from 'backbone.marionette';
import _ from 'underscore';
import BaseView from 'brander-eav/view/base';
import 'backbone-chaining'; // for special model binder's names

export default Marionette.ItemView.extend({
  initialize() {
    BaseView.prototype.initialize.apply(this, arguments);
    Marionette.ItemView.prototype.initialize.apply(this, arguments);
  },
  onChangeAggregations(aggregations) {
    // may be implement
    return false;// doesn't need redraw
  },
  getGeoPointFilter(latitude, longitude, distance, unit) {
    latitude = parseFloat(latitude);
    longitude = parseFloat(longitude);
    if (!latitude || !longitude || _.isNaN(latitude) || _.isNaN(longitude)) {
      throw 'wrong point';
    }
    distance = parseFloat(distance);
    if (!distance || _.isNaN(distance)) {
      throw 'wrong distance';
    }
    if (!unit) {
      unit = 'km';
    }
    return '' + latitude + ',' + longitude + 'distance:' + distance + unit;
  },
  getRangeQuery(lt, gt, lte, gte) {
    let tmp = '';
    if (lt !== undefined) {
      tmp += 'lt:' + lt + ';';
    } else {
      if (lte !== undefined) {
        tmp += 'lte:' + lte + ';';
      }
    }
    if (gt !== undefined) {
      tmp += 'gt:' + gt + ';';
    } else {
      if (gte !== undefined) {
        tmp += 'gte:' + gte + ';';
      }
    }
    return tmp;
  },
  getRangeData(string, formatter) {
    let keywords = [
        'gt',
        'gte',
        'lt',
        'lte',
      ],
      result   = {},
      res;
    _.each(keywords, function (keyword) {
      let format = keyword + ':\\s*(.+?)\\s*;';
      format = new RegExp(format, 'i');
      res = string.match(format);// , $value, $res
      if (res && res.length > 1) {
        if (formatter) {
          result[keyword] = formatter(res[1]);
        } else {
          result[keyword] = res[1];
        }
      }
    });
    return result;
  },
  getPointFromString(string) {
    const tmp = string.split(',');
    return {
      lat: parseFloat(tmp[0]),
      lon: parseFloat(tmp[1]),
    };
  },
  getGeoFilterData(string) {
    let value = string.trim();
    if (value) {
      if (value.indexOf('distance:') !== -1) {
        value = value.split('distance:');
        let distance = value[1];
        value = value[0];
        value = this.getPointFromString(value);
        const units = [
          'km',
          'm',
          'mi',
          'yd',
          'ft',
          'nm',
        ];
        const matches = distance.match('/\\s*([\\d\\.,]+)(.*)$/');
        if (matches[1] && matches[1] !== '') {
          distance = parseFloat(matches[1]);
          let unit;
          if (distance && (unit = matches[2].trim().toLowerCase()) !== '') {
            if (units.indexOf(unit) === -1) {
              unit = 'km';
            }
          } else {
            unit = 'km';
          }
          value.distance = distance;
          value.unit = unit;
          return value;
        } else {
          return {};
        }
      }
    }
    return {};
  },
});
