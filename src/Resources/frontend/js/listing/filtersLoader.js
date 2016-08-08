import $ from 'jquery';
import _ from 'underscore';
import Routing from 'router';


export default {
  load(name, req, onLoad) {
    $.ajax({
      url: Routing.router.generate('brander_eav_filter_list'), // !!
      success(data) {
        requirejs(data, function (view) {
          const resHash = {};
          _.each(arguments, function (module, i) {
            resHash[data[i]] = module;
          });
          onLoad(resHash);
        });
      },
    });
  },
};
