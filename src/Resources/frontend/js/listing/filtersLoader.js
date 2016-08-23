import $ from 'jquery';
import _ from 'underscore';
import router from 'router';

function load(name, req, onLoad) {
  $.ajax({
    url: router.generate('brander_eav_filter_list'), // !! todo: убрать нахуй ПХП часть
    success(data) {
      (req || requirejs)(data, function doViewLoad(...views) {
        const resHash = {};
        _.each(views, (module, i) => {
          let view = module;
          if (module.__esModule === true) {
            view = module.default;
          }
          resHash[data[i]] = view;
        });
        onLoad(resHash);
      });
    },
  });
}

export {load};
