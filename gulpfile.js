// Project configuration
var buildInclude = [
        // include common file types
        '**/*.php',
        '**/*.html',
        '**/*.css',
        '**/*.js',
        '**/*.svg',
        '**/*.ttf',
        '**/*.otf',
        '**/*.eot',
        '**/*.woff',
        '**/*.woff2',
        '**/*.png',
        
        // include specific files and folders
        'screenshot.png',
        'readme.txt',

        // exclude files and folders
        '!./composer.json', 
        '!./composer.lock',
        '!./gulpfile.js',
        '!./{node_modules,node_modules/**/*}',
        '!./package.json',
        '!./phpcs.ruleset.xml',
        '!./{sass,sass/**/*}',
        '!./.stylelintrc',
        '!./{vendor,vendor/**/*}',
        '!svn/**'
    ];
    
var phpSrc = [
        '**/*.php', // Include all files    
        '!node_modules/**/*', // Exclude node_modules
        '!vendor/**' // Exclude vendor   
    ];

var cssInclude = [
        // include css
        '**/*.css',

        // exclude files and folders
        '!**/*.min.css',
        '!node_modules/**/*',
        '!style.css.map',
        '!vendor/**'
    ];
    
var jsInclude = [
        // include js
        '**/*.js',

        // exclude files and folders
        '!**/*.min.js',
        '!node_modules/**/*',
        '!vendor/**',
        '!**/gulpfile.js'       
    ];    

// Load plugins
var gulp = require('gulp'),
    autoprefixer = require('gulp-autoprefixer'), // Autoprefixing magic
    minifycss = require('gulp-uglifycss'),
    filter = require('gulp-filter'),
    uglify = require('gulp-uglify'),
    imagemin = require('gulp-imagemin'),
    newer = require('gulp-newer'),
    rename = require('gulp-rename'),
    concat = require('gulp-concat'),
    notify = require('gulp-notify'),
    runSequence = require('run-sequence'),
    sass = require('gulp-sass'),
    plugins = require('gulp-load-plugins')({
        camelize: true
    }),
    ignore = require('gulp-ignore'), // Helps with ignoring files and directories in our run tasks
    plumber = require('gulp-plumber'), // Helps prevent stream crashing on errors
    cache = require('gulp-cache'),
    sourcemaps = require('gulp-sourcemaps'),
    jshint = require('gulp-jshint'), // JSHint plugin
    stylish = require('jshint-stylish'), // JSHint Stylish plugin
    stylelint = require('gulp-stylelint'), // stylelint plugin
    phpcs = require('gulp-phpcs'), // Gulp plugin for running PHP Code Sniffer.
    phpcbf = require('gulp-phpcbf'), // PHP Code Beautifier
    gutil = require('gulp-util'), // gulp util
    zip = require('gulp-zip'), // gulp zip
    beautify = require('gulp-jsbeautifier'),
    cssbeautify = require('gulp-cssbeautify');

/**
 * Styles
 */
 
// compile sass
gulp.task('sass', function () {
    gulp.src('**/sass/*.scss')
        .pipe(plumber())
        .pipe(sourcemaps.init())
        .pipe(sass({
            errLogToConsole: true,
            outputStyle: 'nested',
            precision: 10
        }))
        .pipe(sourcemaps.write({
            includeContent: false
        }))
        .pipe(sourcemaps.init({
            loadMaps: true
        }))
        .pipe(autoprefixer('last 2 version', '> 1%', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4'))
        .pipe(sourcemaps.write('.'))
        .pipe(plumber.stop())
        .pipe(gulp.dest('./'))
});

// minify all css
gulp.task('mincss', function () {
    gulp.src(cssInclude)
        .pipe(plumber())
        .pipe(sourcemaps.init())
        .pipe(sourcemaps.write({
            includeContent: false
        }))
        .pipe(sourcemaps.init({
            loadMaps: true
        }))
        .pipe(sourcemaps.write('.'))
        .pipe(plumber.stop())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(minifycss({
            maxLineLen: 80
        }))
        .pipe(gulp.dest('./'))
});

// css linting with Stylelint.
gulp.task('lintcss', function lintCssTask() {
  return gulp.src(cssInclude)
    .pipe(stylelint({
      reporters: [
        {formatter: 'string', console: true}
      ]
    }));
});

// make pretty
gulp.task('beautifycss', () =>
    gulp.src(cssInclude)
        .pipe(cssbeautify())
        .pipe(gulp.dest('./'))
);	

/**
 * Scripts
 */

// min all js files
gulp.task('scripts', function () {
    return gulp.src(jsInclude)
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(uglify())
        .pipe(gulp.dest('./'))
});

// js linting with JSHint.
gulp.task('lintjs', function() {
  return gulp.src(jsInclude)
    .pipe(jshint())
    .pipe(jshint.reporter(stylish));
});

// combine scripts into one file and min it.
gulp.task('scriptscombine', function () {
    return gulp.src(jsInclude)
        .pipe(concat('scripts.js'))
        .pipe(gulp.dest('./inc/js'))
        .pipe(rename({
            basename: "scripts",
            suffix: '.min'
        }))
        .pipe(uglify())
        .pipe(gulp.dest('./inc/js/'))
        .pipe(notify({
            message: 'Scripts combined',
            onLast: true
        }));
});

// make pretty
gulp.task('beautifyjs', () =>
    gulp.src(jsInclude)
        .pipe(beautify())
        .pipe(gulp.dest('./'))
);

/**
 * PHP
 */

// PHP Code Sniffer.
gulp.task('phpcs', function () {
    return gulp.src(phpSrc)
        // Validate files using PHP Code Sniffer
        .pipe(phpcs({
            bin: 'vendor/bin/phpcs',
            standard: './phpcs.ruleset.xml',
            warningSeverity: 0
        }))
        .pipe(phpcs.reporter('log')); // Log all problems that was found
});

// PHP Code Beautifier.
gulp.task('phpcbf', function () {
    return gulp.src(phpSrc)
        .pipe(phpcbf({
            bin: 'vendor/bin/phpcbf',
            standard: './phpcs.ruleset.xml',
            warningSeverity: 0
        }))       
        .on('error', gutil.log)
        .pipe(gulp.dest('./'));
});

// ==== TASKS ==== //

// gulp zip
gulp.task('zip', function () {
  return gulp.src(buildInclude)
    .pipe(zip('gulp-standards.zip'))
    .pipe(gulp.dest('./../'));
});  

// Package Distributable
gulp.task('build', function (cb) {
    runSequence('styles', 'scripts', 'zip', cb);
});

// Styles task
gulp.task('styles', function (cb) {
    runSequence('sass', 'mincss', cb);
});


// Watch Task
gulp.task('default', ['styles', 'scripts'], function () {
    gulp.watch('./sass/**/*', ['sass']);
    gulp.watch('./js/**/*.js', ['scripts']);
});
