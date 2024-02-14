"use strict";

var gulp = require("gulp");
var sass = require("gulp-sass")(require("node-sass"));
var concat = require("gulp-concat");

gulp.task("scss", function () {
    return gulp
        .src("public/scss/main.scss")
        .pipe(sass())
        .pipe(concat("main.css"))
        .pipe(gulp.dest("public/css"));
});

gulp.task("scss:watch", function () {
    gulp.watch("public/scss/**/*.scss", gulp.series("scss"));
});
