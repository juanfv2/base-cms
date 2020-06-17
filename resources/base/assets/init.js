
/**
 * Para assets cambiar "/s+/"
 *
 * # saldo - 1
 */

// var host = 'http://l.splus.net';
var host = 'http://192.168.1.3:8000';

var api = host + '/api';
var verify = api + '/user/verify/';
var reset = api + '/password/reset';

function findGetParameter(parameterName) {
  var result = null,
    tmp = [];
  location.search
    .substr(1)
    .split("&")
    .forEach(function (item) {
      tmp = item.split("=");
      if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
    });
  return result;
}