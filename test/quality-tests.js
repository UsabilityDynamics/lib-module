//console.log( typeof mochaphpcs );

module.exports = {

  Quality: require( 'mocha-phpcs' ).define.call( this, {
    standard: 'PSR2',
    ignoreExitCode: true,
    warningSeverity: 1,
    report: 'full',
    reportWidth: 200,
    reportFile: 'static/wiki/PHP-CS.md',
    tabWidth: 2,
    dir: [ 'lib/*.php' ]
  })

}
