/**
 * Install:
 * npm install
 */


// modules
const  gulp = require('gulp');
const  changed = require('gulp-changed');
const  compass = require('gulp-compass');
const  sourcemaps = require('gulp-sourcemaps');
const  filter = require('gulp-filter');
const  uglify = require('gulp-uglify');
const  concat = require('gulp-concat');
const  livereload = require('gulp-livereload');
const  rename = require("gulp-rename");
const  babel = require("gulp-babel");

// app config
var appConfig = require('./src/config.json');


// paths
var paths = {
    src: {
        sass: 'src/sass/**/*.scss',
        js: 'src/js/**/*.js'
    },

    dest: {
        css: 'www/css',
        js: 'www/js'
    },

    reloadWatch: [
        'www/js/**/*.js',
        'www/css/**/*.css',
        'www/img/**/*',
        'app/presenters/templates/**/*.latte'
    ]
};


// reload timeout setup
var reloadDelay = 3000;
var reloadTimeout;


// error handling, prevent gulp from exiting on error
function onError(err) {
    console.log(err.toString());
    this.emit('end');
}

// copy static assets
gulp.task('copy_assets', function () {
    for (var i = 0; i < appConfig.copyAssets.length; i++) {
        var item = appConfig.copyAssets[i];
        if (item.src && item.dest && item.type == "plugin") {
            gulp.src(item.src).pipe(rename(function (path) {
                path.basename = "_" + path.basename;
                path.extname = ".scss"
            })).pipe(gulp.dest(item.dest));
        } else {
            gulp.src(item.src).pipe(gulp.dest(item.dest));
        }
    }
});

// JS, compile in order of jsBundles config array
gulp.task('scripts-lib', function () {
    var srcJs = appConfig.libJs.src;
    var destJs = appConfig.libJs.dest;
    return gulp.src(srcJs)
        .pipe(sourcemaps.init())
        .pipe(concat(destJs))
        .pipe(uglify())
        .on('error', onError)
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(paths.dest.js));
});

// JS, compile in order of jsBundles config array
gulp.task('scripts', function () {
    var srcJs = appConfig.appJs.src;
    var destJs = appConfig.appJs.dest;
    return gulp.src(srcJs)
        .pipe(babel({
            "presets": ["@babel/env"]
        }).on('error', onError))
        .pipe(sourcemaps.init())
        .pipe(concat(destJs))
        .pipe(uglify())
        .on('error', onError)
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(paths.dest.js));
});

// JS, compile in order of jsBundles config array
gulp.task('scripts-watch', function () {
    // watch for script changes
    gulp.watch(paths.src.js, ['scripts']);
});


// SASS + compass
gulp.task('compass', function () {
    return gulp.src(paths.src.sass)
        .pipe(compass({
            config_file: 'src/config.rb',
            css: paths.dest.css,
            sass: 'src/sass',
            sourcemap: true
        }))
        .on('error', onError)
        .pipe(gulp.dest(paths.dest.css));
});


// watch for file updates
gulp.task('watch', function () {
    livereload.listen();

    // watch for sass changes
    gulp.watch(paths.src.sass, ['compass']);

    // watch for script changes
    gulp.watch(paths.src.js, ['scripts']);

    // watch for livereload
    gulp.watch(paths.reloadWatch).on('change', function (file) {
        if (reloadDelay > 0) {
            // delayed reload
            if (reloadTimeout) {
                clearTimeout(reloadTimeout);
            }
            reloadTimeout = setTimeout(function () {
                livereload.changed(file.path);
            }, reloadDelay);

        } else {
            // instant reload
            livereload.changed(file.path);
        }
    });

});

gulp.task('default', ['scripts-lib', 'scripts', 'compass', 'copy_assets']);

