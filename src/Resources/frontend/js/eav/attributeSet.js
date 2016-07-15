import Backbone from 'backbone';
import Routing from 'router';
import BaseModel from 'brander-eav/basemodel';
import AttributeModel from './attribute';
import AttributeCollection from './attributeCollection';


var Model = BaseModel.extend({
  'relations': [
    {
      'type':           Backbone.HasMany,
      'key':            'attributes',
      'relatedModel':   AttributeModel,
      'collectionType': AttributeCollection,
    },
  ],

  'url'() {
    return this.isNew()
      ? Routing.generate('brander_eav_attribute_set_post')
      : Routing.generate('brander_eav_attribute_set_get', {'attributeSet': this.id});
  },
});

export default Model;
