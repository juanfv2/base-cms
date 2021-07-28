var token = findGetParameter('t')
var urlStr = _verify + token
console.log('urlStr', urlStr);

$.post(urlStr, function (resp) {
    console.log('data', resp);
  $('.r-title').html(resp.data.title)
  $('.r-description').html(resp.data.description)
}).fail(function (resp) {
  console.log('data', resp)
  $('.r-title').html(resp.responseJSON.data.title)
  $('.r-description').html(resp.responseJSON.data.description)
})
