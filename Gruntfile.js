module.exports = function(grunt) {
  grunt.initConfig({
    less: {
      core: {
        files: {
          'web/css/style.css': 'web/css/style.less'
        }
      }
    },
    watch: {
      less: {
        options: { livereload: true },
        files: ['web/css/*.less'],
        tasks: ['less']
      }
    }
  });
  grunt.registerTask('default', ['less', 'concat', 'uglify']);

  grunt.loadNpmTasks("grunt-contrib-less");
  grunt.loadNpmTasks("grunt-contrib-watch");
}

