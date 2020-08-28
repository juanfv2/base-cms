
var token = findGetParameter('t');
var urlStr = _verify + token;
// console.log('urlStr', urlStr);

$
    .post(urlStr, function (data) {
        $('.r-title').html(data.title);
        $('.r-description').html(data.description);
    })
    .fail(function (data) {
        console.log('data', data);
        $('.r-title').html(data.responseJSON.title);
        $('.r-description').html(data.responseJSON.description);
    });
