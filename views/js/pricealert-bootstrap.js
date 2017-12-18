(function() {
  window.showPriceAlert = false;
  window.PriceAlert = function(show) {
    if (show) {
      window.showPriceAlert = true;
      console.warn('Price alert deferred');
    }
  }
  var src = window.priceAlertUrl.replace('ajax.php', 'views/js/pricealert.js');
  var tag = document.createElement('script');
  tag.src = src;
  tag.setAttribute('defer','');
  tag.setAttribute('async','');
  var firstScriptTag = document.getElementsByTagName('script')[0];
  firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
})();
