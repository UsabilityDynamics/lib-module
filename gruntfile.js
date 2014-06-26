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
    bin: {
      phpcs: findup( 'vendor/bin/phpcs' ) || findup( 'phpcs', { cwd: '/usr/bin' } ) || findup( 'phpcs', { cwd: '/usr/local/bin' } ),
      phpunit: findup( 'vendor/bin/phpunit' ) || findup( 'phpunit', { cwd: '/usr/bin' } ) || findup( 'phpunit', { cwd: '/usr/local/bin' } )
    },
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

    // CLI Commands.
    shell: {
      install: {
        options: { stdout: true },
        command: 'composer install --prefer-dist --dev --no-interaction --quiet'
      },
      update: {
        options: { stdout: true },
        command: 'composer update --prefer-source --no-interaction --quiet'
      }
    },

    // Tests.
    mochaTest: {
      options: {
        timeout: 10000,
        log: false,
        require: [ 'should' ],
        reporter: 'list',
        ui: 'exports'
      },
      main: {
        src: [ 'test/lib-module.js' ]
      },
      unit: {
        src: [ 'test/unit-*.js' ]
      },
      quality: {
        src: [ 'test/quality-*.js' ]
      },
      acceptance: {
        src: [ 'test/acceptance/*.js' ]
      }
    }

  });

  // Register NPM Tasks.
  grunt.registerTask( 'default', function() {

    grunt.task.run( 'mochaTest' );

    if( grunt.config.get( 'meta.ci' ) ) {
      // grunt.task.run( 'test:quality' );
    }

  });

  // Run Quick Tests.
  grunt.registerTask( 'test', 'Essential tests.', function() {
    grunt.task.run( 'clean:composer' );
    grunt.task.run( 'mochaTest:main' );
    grunt.task.run( 'mochaTest:unit' );
  });

  // Run Acceptance Tests.
  grunt.registerTask( 'acceptance', 'Involved tests.', function() {
    grunt.task.run( 'clean:composer' );
    grunt.task.run( 'mochaTest:acceptance' );
  });

  // Publish Library.
  grunt.registerTask( 'publish', function() {

  });

  // Pre-Publish Library.
  grunt.registerTask( 'prepublish', function() {

  });

  // Install Library.
  grunt.registerTask( 'install', function() {

  });

};