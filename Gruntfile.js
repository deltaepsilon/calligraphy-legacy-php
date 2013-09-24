module.exports = function(grunt) {
  grunt.initConfig({
    less: {
      core: {
        files: {
          'web/css/style.css': 'web/css/style.less'
        }
      }
    },
    coffee: {
      core: {
        files: {
          'web/js/script.js': 'web/js/script.coffee'
        }
      }
    },
    watch: {
      less: {
        options: { livereload: true },
        files: ['web/css/*.less'],
        tasks: ['less']
      },
      coffee: {
        options: { livereload: true },
        files: ['web/js/*.coffee'],
        tasks: ['coffee']
      }
    }
  });
  grunt.registerTask('default', ['less', 'coffee']);

  grunt.loadNpmTasks("grunt-contrib-less");
  grunt.loadNpmTasks("grunt-contrib-coffee");
  grunt.loadNpmTasks("grunt-contrib-watch");

}

