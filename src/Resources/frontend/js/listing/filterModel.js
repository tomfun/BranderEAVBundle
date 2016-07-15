import BaseModel from 'brander-eav/basemodel';
import Backbone from 'backbone';
import AttributeModel from 'brander-eav/eav/attribute';
import 'backbone.relational';


var Model = BaseModel.extend({
  'defaults':  {
    'field':        [],
    'isFilterable': false,
    'isSortable':   false,
    'view':         'brander-eav/listing/simpleFilterView',
    'viewOptions':  {},
    'viewOrder':    0,
  },
  'relations': [
    {
      'type':         Backbone.HasOne,
      'key':          'attribute',
      'relatedModel': AttributeModel,
    },
  ],
});

export default Model;
