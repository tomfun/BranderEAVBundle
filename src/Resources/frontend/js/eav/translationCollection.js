import _ from 'lodash';
import Backbone from 'backbone';
import BaseModel from 'brander-eav/basemodel';


var Model      = BaseModel.extend({}),
  Collection = Backbone.Collection.extend({
    'model': Model,
    indexOfLocale(locale, createNew) {
      var index = _.findIndex(this.toArray(), function (model) {
        return model.get('locale') === locale;
      });
      if (index !== -1) {
        return index;
      }
      if (createNew === undefined || createNew === true) {
        this.add(new Model({locale}));
        return this.indexOfLocale(locale, false);
      }
    },
  });

Collection.Model = Model;

export default Collection;
