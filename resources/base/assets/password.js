const token = findGetParameter('t')
const email = decodeURIComponent(findGetParameter('e'))
const model = {}

$('#token').val(token)
$('#email').val(email)

$(document).ready(function () {
  $('#btn-send').click(function () {
    send()
  })
})

function send() {
  const password = $('#password').val()
  const confirmation = $('#password_confirmation').val()

  model.token = token
  model.email = email
  model.password = password
  model.password_confirmation = password

  // console.log('password',  password);
  // console.log('model',     model);
  // console.log('urlStr',    _reset);

  if (password == '') {
    $('.error-password').addClass('d-block')
    return
  }
  if (confirmation == '') {
    $('.error-confirmation').addClass('d-block')
    return
  }

  if (password != confirmation) {
    $('.error-not-equal').addClass('d-block')
    return
  }

  $.post(_reset, model, function (data) {
    $('.r-description').html(data.message)
    $('.success').addClass('d-block')
    $('.d-form').hide()
    $('.msg-success').addClass('d-block')
  }).fail(function (data) {
    $('.r-description').html(data.responseJSON.message)
    $('.errors').addClass('d-block')
  })
}
