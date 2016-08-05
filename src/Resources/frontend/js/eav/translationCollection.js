import _ from 'lodash';
import Backbone from 'backbone';
import BaseModel from 'brander-eav/basemodel';


const Model = BaseModel.extend({});
const Collection = Backbone.Collection.extend({
  model: Model,
  indexOfLocale(locale, createNew) {
    const index = _.findIndex(this.toArray(), function (model) {
      return model.get('locale') === locale;
    });
    if (index !== -1) {
      return index;
    }
    if (createNew === undefined || createNew === true) {
      this.add(new Model({locale}));
      return this.indexOfLocale(locale, false);
    }
    return null;
  },
});

Collection.Model = Model;

const AttributeTranslation = Model.extend({});
const AttributeTranslationCollection = Collection.extend({});

const AttributeGroupTranslation = Model.extend({});
const AttributeGroupTranslationCollection = Collection.extend({});

const OptionTranslation = Model.extend({});
const OptionTranslationCollection = Collection.extend({});

export default Collection;
export {
  AttributeTranslation,
  AttributeGroupTranslation,
  OptionTranslation,
  OptionTranslationCollection,
  AttributeGroupTranslationCollection,
  AttributeTranslationCollection,
};
