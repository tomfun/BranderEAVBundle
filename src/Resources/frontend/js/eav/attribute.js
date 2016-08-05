import $ from 'jquery';
import Backbone from 'backbone';
import BaseModel from 'brander-eav/basemodel';
import Routing from 'router';
import OptionCollection from './optionCollection';
import {AttributeTranslationCollection, AttributeTranslation} from './translationCollection';


var Model = BaseModel.extend({
  'url'() {
    return this.isNew()
      ? Routing.generate('brander_eav_attribute_post')
      : Routing.generate('brander_eav_attribute_get', {'attribute': this.id});
  },

  'check'() {
    if (this.isNew()) {
      return $.Deferred().resolve();
    }
    var url = Routing.generate('brander_eav_attribute_check', {'attribute': this.id});
    return $.ajax({
      url,

      'method':      'patch',
      'dataType':    'json',
      'processData': false,
      'data':        JSON.stringify(this.toJSON()),
    });
  },

  'hasOptions'() {
    return this.get('discr') === 'select';
  },

  getTitle() {
    return this.get('translations').at(0).get('title');
  },

  'relations': [
    {
      'type':           Backbone.HasMany,
      'key':            'options',
      'relatedModel':   OptionCollection.Model,
      'collectionType': OptionCollection,
    },
    {
      'type':           Backbone.HasMany,
      'key':            'translations',
      'relatedModel':   AttributeTranslation,
      'collectionType': AttributeTranslationCollection,
    },
  ],

  'defaults': {
    'isRequired':   false,
    'isFilterable': false,
    'isSortable':   false,
    'discr':        'input',
  },
});

export default Model;
