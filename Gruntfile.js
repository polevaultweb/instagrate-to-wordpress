module.exports = function( grunt ) {

	// load all grunt tasks in package.json matching the `grunt-*` pattern
	require( 'load-grunt-tasks' )( grunt );

	// Project configuration
	grunt.initConfig( {
		pkg:    grunt.file.readJSON( 'package.json' ),
		clean: {
			main: ['release'],
			post: ['release/<%= pkg.version %>/composer.lock', 'release/<%= pkg.version %>/composer.json']
		},
		copy: {
			// Copy the plugin to a versioned release directory
			main: {
				src:  [
					'**',
					'!node_modules/**',
					'!release/**',
					'!.git/**',
					'!.github/**',
					'!.sass-cache/**',
					'!assets/css/src/**',
					'!assets/css/**/*.map',
					'!assets/js/src/**',
					'!img/src/**',
					'!Gruntfile.js',
					'!package.json',
					'!.gitignore',
					'!.gitmodules',
					'!yarn.lock',
					'!README.md',
					'!vendor/**',
					'!phpstan.neon.dist',
				],
				dest: 'release/<%= pkg.version %>/'
			}
		},
		composer : {
			options : {
				usePhp: true,
				flags: ['no-dev'],
				cwd: 'release/<%= pkg.version %>/',
				composerLocation: '/usr/local/bin/composer'
			},
		},
		compress: {
			main: {
				options: {
					mode: 'zip',
					archive: './release/<%= pkg.name %>.zip'
				},
				expand: true,
				cwd: 'release/<%= pkg.version %>/',
				src: ['**/*'],
				dest: '<%= pkg.name %>/'
			}
		},
		makepot: {
			target: {
				options: {
					exclude: [
						'assets/.*', 'images/.*', 'node_modules/.*', 'tests/.*', 'release/.*', 'build/.*'
					],
					domainPath: '/languages',
					mainFile: 'instagrate-to-wordpress.php',
					potFilename: '<%= pkg.name %>.pot',
					potHeaders: {
						poedit: true,                 // Includes common Poedit headers.
						'x-poedit-keywordslist': true // Include a list of all possible gettext functions.
					},
					type: 'wp-plugin'
				}
			}
		},
	} );

	grunt.registerTask( 'do_pot', ['makepot'] );
	grunt.registerTask( 'build', ['clean', 'copy', 'composer:install', 'clean:post','compress'] );
};
