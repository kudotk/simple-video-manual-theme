export default {
  alert: function (message) {
    return alert(message)
  },

  // show error
  error: function (message, direction) {
    return alert(message + '\r' + direction)
  },

  // show confirm
  confirm: function (message) {
    return confirm(message)
  }
}
