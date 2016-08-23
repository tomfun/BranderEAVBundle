import _ from 'lodash';
import Backbone from 'backbone';
import BaseModel from 'brander-eav/basemodel';
import AttributeModel from './attribute';
import AttributeCollection from './attributeCollection';
import Routing from 'router';
import {AttributeGroupTranslationCollection, AttributeGroupTranslation} from './translationCollection';

const Model = BaseModel.extend({
  url() {
    return this.isNew()
      ? Routing.generate('brander_eav_attribute_group_post')
      : Routing.generate('brander_eav_attribute_group_get', {'attributeGroup': this.id});
  },

  relations: [
    {
      'type':           Backbone.HasMany,
      'key':            'attributes',
      'relatedModel':   AttributeModel,
      'collectionType': AttributeCollection,
    },
    {
      'type':           Backbone.HasMany,
      'key':            'translations',
      'relatedModel':   AttributeGroupTranslation,
      'collectionType': AttributeGroupTranslationCollection,
    },
  ],

  getIds() {
    const hash = {};
    this.get('attributes').each((attr) => {
      hash[attr.id] = true;
    });
    return hash;
  },

  filterValues(values) {
    let ids = {},
      res = [];
    if (values instanceof Backbone.Collection) {
      ids = this.getIds();
      values.each(function (val) {
        let attribute;
        if (val && (attribute = val.get('attribute'))) {
          if (attribute instanceof AttributeModel && ids[String(attribute.id)]) {
            res.push(val);
          }
        }
      });
      return res;
      // } else {
      //    ids = this.getIds();
      //    if (_.isArray(values)) {
      //
      //    }
    }
    throw 'not a collection';
  },
});

export default Model;
