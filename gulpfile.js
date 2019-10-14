var gulp = require("gulp"),
  sass = require("gulp-sass"),
  autoprefixer = require("gulp-autoprefixer"),
  livereload = require("gulp-livereload"),
  sourcemaps = require("gulp-sourcemaps");

gulp.task("sass", function() {
  gulp
    .src(["./assets/scss/seeds-page.scss","./assets/scss/backend.scss"])
    .pipe(sourcemaps.init())
    .pipe(sass().on("error", sass.logError))
    .pipe(autoprefixer("last 2 version"))
    .pipe(sourcemaps.write("./"))
    .pipe(gulp.dest("./assets/css"));
});

gulp.task("build", function() {
  gulp.start(["sass"]);
});

gulp.task("watch", ["sass"], function() {
  livereload.listen();
  gulp.watch("./assets/scss/**/*.scss", ["sass"]);
  gulp.watch(["./assets/css/seeds-page.css","./assets/css/backend.css"], function(files) {
    livereload.changed(files);
  });
});
