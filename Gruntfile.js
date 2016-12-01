module.exports = function (grunt) {
    // Project configuration.
    grunt.initConfig({
        uglify: {
            pdopage: {
                src: [
                    'assets/components/pdotools/js/pdopage.js'
                ],
                dest: 'assets/components/pdotools/js/pdopage.min.js'
            },
            jquery_pdopage: {
                src: [
                    'assets/components/pdotools/js/jquery.pdopage.js'
                ],
                dest: 'assets/components/pdotools/js/jquery.pdopage.min.js'
            },
            jquery_sticky: {
                src: [
                    'assets/components/pdotools/js/lib/jquery.sticky.js'
                ],
                dest: 'assets/components/pdotools/js/lib/jquery.sticky.min.js'
            }
        },
        cssmin: {
            pdopage: {
                src: [
                    'assets/components/pdotools/css/pdopage.css'
                ],
                dest: 'assets/components/pdotools/css/pdopage.min.css'
            }
        },
        watch: {
            scripts: {
                files: ['assets/components/pdotools/**/*.js'],
                tasks: ['uglify']
            },
            css: {
                files: ['assets/components/pdotools/**/*.css'],
                tasks: ['cssmin']
            }
        }
    });

    //load the packages
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-banner');
    grunt.loadNpmTasks('grunt-ssh');
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-postcss');
    grunt.loadNpmTasks('grunt-string-replace');
    grunt.renameTask('string-replace', 'bump');

    //register the task
    grunt.registerTask('default', ['uglify', 'cssmin']);
};