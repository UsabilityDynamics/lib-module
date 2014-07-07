/**
 * Library Build.
 *
 * @author peshkov@UD
 * @version 1.1.2
 * @param grunt
 */
module.exports = function build( grunt ) {

  // Require Utility Modules.
  var joinPath  = require( 'path' ).join;
  var findup    = require( 'findup-sync' );

  // Determine Paths.
  var _paths = {
    composer: findup( 'composer.json' ),
    phpcs: findup( 'vendor/bin/phpcs' ) || findup( 'phpcs', { cwd: '/usr/bin' } ),
    vendor: findup( 'vendor' ),
    jsTests: findup( 'test' ),
    staticFiles: findup( 'static' )
  };

  // Automatically Load Tasks.
  require( 'load-grunt-tasks' )( grunt, {
    pattern: 'grunt-*',
    config: './package.json',
    scope: 'devDependencies'
  });

  grunt.initConfig({
    
    // Read Composer File.
    composer: grunt.file.readJSON( 'composer.json' ),
    
    // Sets generic config settings, callable via grunt.config.get('meta').environment or <%= grunt.config.get("meta").environment %>
    meta: {
      ci: process.env.CI || process.env.CIRCLECI ? true : false,
      environment: process.env.NODE_ENV || 'production'
    },    

    // Generate Documentation.
    yuidoc: {
      compile: {
        name: '<%= composer.name %>',
        description: '<%= composer.description %>',
        version: '<%= composer.version %>',
        url: '<%= composer.homepage %>',
        options: {
          paths: 'lib',
          outdir: 'static/codex/'
        }
      }
    },

    /**
     * Runs PHPUnit Tests
     *
     */
    phpunit: {
      classes: {},
      options: {
        bin: './vendor/bin/phpunit',
      },
      local: {
        configuration: './test/php/phpunit.xml'
      },
      circleci: {
        configuration: './test/php/phpunit-circle.xml'
      }
    },

    phpcs: {
      options: {
        bin: _paths.phpcs,
        standard: 'PSR2',
        warningSeverity: 1,
        reportFile: 'static/wiki/PHP-CS.md'
      },
      application: {
        dir: [ 'lib/*.php' ]
      }
    },

    // Development Watch.
    watch: {
      options: {
        interval: 100,
        debounceDelay: 500
      },
      js: {
        files: [
          'static/scripts/src/*.*'
        ],
        tasks: [ 'uglify' ]
      }
    },

    // Uglify Scripts.
    uglify: {
      production: {
        options: {
          preserveComments: false,
          wrap: false
        },
        files: [
          {
            expand: true,
            cwd: 'static/scripts/src',
            src: [ '*.js' ],
            dest: 'static/scripts'
          }
        ]
      }
    },

    // Generate Markdown.
    markdown: {
      all: {
        files: [
          {
            expand: true,
            src: 'readme.md',
            dest: 'static/',
            ext: '.html'
          }
        ],
        options: {
          markdownOptions: {
            gfm: true,
            codeLines: {
              before: '<span>',
              after: '</span>'
            }
          }
        }
      }
    },

    // Clean for Development.
    clean: {
      composer: [
        "composer.lock"
      ],
      test: [
        ".test"
      ]
    },

    shell: {
      /**
       * Runs PHPUnit test, creates code coverage and sends it to Scrutinizer
       */
      coverageScrutinizer: {
        command: [
          'grunt phpunit:circleci --coverage-clover=coverage.clover',
          'wget https://scrutinizer-ci.com/ocular.phar',
          'php ocular.phar code-coverage:upload --format=php-clover coverage.clover'
        ].join( ' && ' ),
        options: {
          encoding: 'utf8',
          stderr: true,
          stdout: true
        }
      },
      /**
       * Runs PHPUnit test, creates code coverage and sends it to Code Climate
       */
      coverageCodeClimate: {
        command: [
          'grunt phpunit:circleci --coverage-clover build/logs/clover.xml',
          'CODECLIMATE_REPO_TOKEN='+ process.env.CODECLIMATE_REPO_TOKEN + ' ./vendor/bin/test-reporter'
        ].join( ' && ' ),
        options: {
          encoding: 'utf8',
          stderr: true,
          stdout: true
        }
      },
      // CLI Command.
      install: {
        options: { stdout: true },
        command: 'composer install --prefer-dist --dev --no-interaction --quiet'
      },
      // CLI Command.
      update: {
        options: { stdout: true },
        command: 'composer update --prefer-source --no-interaction --quiet'
      }
    },

    // Tests.
    mochaTest: {
      options: {
        timeout: 10000,
        log: true,
        require: [ 'should' ],
        reporter: 'mocha-audit-reporter',
        ui: 'exports'
      },
      basic: {
        src: [ 'test/*.js' ]
      }
    }

  });

  
  // Register NPM Tasks.
  grunt.registerTask( 'default', [ 'config:staging' ], function() {

    grunt.task.run( 'mochaTest' );
    
    if( grunt.config.get( 'meta.ci' ) ) {
      // grunt.task.run( 'test:quality' );
    }
    
  });

  // Run coverage tests.
  grunt.registerTask( 'testscrutinizer', [ 'shell:coverageScrutinizer' ] );
  grunt.registerTask( 'testcodeclimate', [ 'shell:coverageCodeClimate' ] );
  
  // Run Local Tests
  grunt.registerTask( 'localtest', [ 'phpunit:local' ] );
  
  // Build Distribution.
  grunt.registerTask( 'publish', [ 'markdown', 'yuidoc' ] );

  // Update Environment.
  grunt.registerTask( 'update', [ 'clean:update', 'shell:update' ] );

};