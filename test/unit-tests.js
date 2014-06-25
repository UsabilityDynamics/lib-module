/**
 *
 * @type {{phpUnit: *}}
 */
module.exports = {

  phpUnit: require( 'mocha-phpunit' ).define({
    dir: 'test/classes/',
    config: 'test/phpunit.xml'
  })

};