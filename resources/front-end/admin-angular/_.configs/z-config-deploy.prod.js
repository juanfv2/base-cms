/* -------------------------------------------------------------------------- */
/* npm i -g replace-in-file                                                   */
/* -------------------------------------------------------------------------- */

const now = new Date().toISOString().replace(/[&\/\\#, +()$~%-.'":*?<>{}]/g, '_')

const regexToday = new RegExp('d-*.*Z', 'm')
const regexCatchErrors = new RegExp('/* catch-errors*.*build', 'g')

module.exports = {
  files: ['./src/index.html', 'src/environments/k.ts', './src/app/app.module.ts'],
  from: [regexToday, regexCatchErrors],
  to: [`d-${now}`, ' catch-errors */ // for-build'],
}
