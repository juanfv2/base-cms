
// document.title = '{{appName}} · Bienvenido';

document.title = 'Infrasal · Bienvenido';

// const host = 'http://l.splus.net/';
// const host = 'http://127.0.0.1:8000/';
const host = '/';
const api = host + 'api/';
const _verify = api + 'user/verify/';
const _reset = api + 'password/reset';

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
