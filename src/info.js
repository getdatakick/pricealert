'use strict';

var _ = require('underscore');
var json = require('./build/metadata/stats.json');

console.log(getVendorScripts(json, 'app'));

function getVendorScripts(j, chunkName) {
  var chunk = _.find(j.chunks, function(c) {
    return c.names[0] === chunkName;
  });

  return _.chain(chunk.modules)
    .map(function(module) {
      var name = module.name.replace(/^.*~[\\\/]([^\\\/]*).*/, "$1");
      var size = module.size;
      return {name: name, size: size};
    })
    .groupBy('name')
    .mapObject(function(value, key) {
      return _.reduce(value, function(a, b) {
        return a + b.size;
      }, 0);
    })
    .map(function(value, key){
      return {name: key, size: value};
    })
    .sortBy(function(v) {
      return -v.size;
    })
    .map(function(module) {
      return module.name+' ('+Math.round(module.size / 1024)+'kB)';
    })
    .uniq()
    .value();
}
