import Backbone from 'backbone';
import BaseModel from 'brander-eav/basemodel';
import TranslationCollection from './translationCollection';


let Model      = BaseModel.extend({
    'defaults':  {
      title: 'new option',
    },
    'relations': [
      {
        'type':           Backbone.HasMany,
        'key':            'translations',
        'relatedModel':   TranslationCollection.Model,
        'collectionType': TranslationCollection,
      },
    ],
  }),
  Collection = Backbone.Collection.extend({
    'model': Model,
  });

Collection.Model = Model;

export default Collection;
