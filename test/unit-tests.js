/**
 *
 * @type {{phpUnit: *}}
 */
module.exports = {

  phpUnit: require( 'mocha-phpunit' ).define({
    dir: 'unit/classes/',
    config: 'unit/phpunit.xml'
  })

};