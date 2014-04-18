module.exports = function (grunt) {

	var target = 'vendor/target';
	var bower = 'vendor/bower';

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		bowerDir: bower,
		targetDir: target,
		buildDir: target + '/build',
		workDir: target + '/work',

		clean: {
			target: [ '<%= targetDir %>' ],
			work: [ '<%= workDir %>' ]
		},

		copy: {
			jquery: {
				src: '<%= bowerDir %>/jquery/dist/jquery.min.js',
				dest: '<%= workDir %>/jquery.min.js'
			},
			jqueryUi: {
				files: [
					{
						src: '<%= bowerDir %>/jquery-ui/themes/base/minified/jquery-ui.min.css',
						dest: '<%= buildDir %>/jquery-ui.min.css'
					},
					{
						expand: true,
						cwd: '<%= bowerDir %>/jquery-ui/themes/base/minified/images',
						src: '**',
						dest: '<%= buildDir %>/images/'
					}
				]
			},
			webshim: {
				src: '<%= bowerDir %>/webshim/js-webshim/minified/shims/styles/shim.css',
				dest: '<%= buildDir %>/shims/styles/shim.css'
			},
			momentjs: {
				src: '<%= bowerDir %>/moment/min/moment-with-langs.min.js',
				dest: '<%= buildDir %>/moment.min.js'
			}
		},

		uglify: {
			options: {
				preserveComments: 'some'
			},
			jqueryCookie: {
				files: {
					"<%= workDir %>/jquery-cookie.min.js": [ '<%= bowerDir %>/jquery-cookie/jquery.cookie.js' ]
				}
			},
			q: {
				files: {
					"<%= buildDir %>/q.min.js": [ '<%= bowerDir %>/q/q.js' ]
				}
			}
		},

		concat: {
			jqueryUi: {
				src: [
					'<%= bowerDir %>/jquery-ui/ui/minified/jquery-ui.min.js',
					'<%= bowerDir %>/jquery-ui/ui/minified/i18n/jquery-ui-i18n.min.js'
				],
				dest: '<%= workDir %>/jquery-ui.min.js'
			},
			webshim: {
				options: {
					footer: "window.html5 = { shivMethods: false};$.webshims.polyfill('es5 geolocation json-storage');"
				},
				src: [
					'<%= bowerDir %>/webshim/js-webshim/minified/extras/modernizr-custom.js',
					'<%= bowerDir %>/webshim/js-webshim/minified/polyfiller.js'
				],
				dest: '<%= workDir %>/polyfiller.min.js'
			},
			merge: {
				src: [
					'<%= workDir %>/jquery.min.js',
					'<%= workDir %>/jquery-cookie.min.js',
					'<%= workDir %>/jquery-ui.min.js',
					'<%= workDir %>/polyfiller.min.js'
				],
				dest: '<%= buildDir %>/jquery.all.min.js'
			}
		}
	});

	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-copy');

	grunt.registerTask('jquery', [ 'copy:jquery' ]);
	grunt.registerTask('jquery-cookie', [ 'uglify:jqueryCookie' ]);
	grunt.registerTask('jquery-ui', [ 'concat:jqueryUi', 'copy:jqueryUi' ]);
	grunt.registerTask('momentjs', [ 'copy:momentjs' ]);
	grunt.registerTask('q', [ 'uglify:q' ]);
	grunt.registerTask('webshim', [ 'concat:webshim', 'copy:webshim' ]);
	grunt.registerTask('merge', [ 'concat:merge' ]);

	grunt.registerTask('default', [
		'clean:target',
		'jquery',
		'jquery-cookie',
		'jquery-ui',
		'momentjs',
		'q',
		'webshim',
		'merge',
		'clean:work'
	]);

};
