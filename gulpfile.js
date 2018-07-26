/**
 * Gulpfile v1.1
 */
const concat = require('gulp-concat');
const es = require('event-stream');
const gulp = require('gulp');
const gulpIf = require('gulp-if');
const moment = require('moment');
const notify = require('gulp-notify');
const headerfooter = require('gulp-headerfooter');
const sass = require('gulp-sass');
const streamqueue = require('streamqueue');
const templateCache = require('gulp-angular-templatecache');
const zip = require('gulp-zip');

const root = __dirname;
const moodleRoot = `${root}/moodle`;
const config = require('./package.json');

let prod = false; // production flag off by default

gulp.task('build', function() {
    return plugins(function(plugin) {
        var pluginRoot = `${moodleRoot}/${plugin}`;
        var pluginName = new String(plugin).split('/')[1];
        var css = streamqueue({objectMode: true}); // stream for buffering compiled CSS
        var js = streamqueue({objectMode: true}); // stream for buffering compiled JS

        // build angular sass
        css.queue(
            gulp.src([`${pluginRoot}/dev/angular/**/*.scss`])
                .pipe(sass())
        );

        // build vue sass
        css.queue(
            gulp.src([`${pluginRoot}/dev/vue/**/*.scss`])
                .pipe(sass())
        );

        // build custom sass with higher priority (later in the chain)
        css.queue(
            gulp.src(`${pluginRoot}/dev/scss/init.scss`)
                .pipe(sass())
        );

        // build angular javascript
        js.queue(
            gulp.src([
                `${pluginRoot}/dev/angular/**/*.module.js`,
                `${pluginRoot}/dev/angular/**/*.js`,
                `!${pluginRoot}/dev/angular/**/*.spec.js`
            ])
        );

        // build angular HTML templates
        js.queue(
            gulp.src([`${pluginRoot}/dev/angular/**/*.html`])
                .pipe(templateCache('templates.js', {
                    module: pluginName,
                    root: '',
                    standAlone: true
                }))
        );
        
        // build vue javascript
        js.queue(
            gulp.src([
                `${pluginRoot}/dev/vue/**/*.js`,
                `!${pluginRoot}/dev/vue/**/*.spec.js`
            ])
        );

        // output CSS
        css.done()
            // .pipe(gulpIf(prod, cleanCSS()))
            .pipe(concat('styles.css'))
            .pipe(gulp.dest(`./moodle/${plugin}`));

        // output JS
        js.done()
            // .pipe(gulpIf(prod, uglify()))
            // .pipe(headerfooter(
            //     `./moodle/${plugin}/dev/amd/header.txt`,
            //     `./moodle/${plugin}/dev/amd/footer.txt`, 
            // ))
            .pipe(concat('javascript.js'))
            .pipe(gulp.dest(`./moodle/${plugin}`));

    });
});

gulp.task('prod', function(done) {
    prod = true;

    return done();
});

gulp.task('publish', ['prod', 'build'], function(done) {
    const file = `${config.name}-${moment().format('YYYYMMDD-HHmm')}.zip`;
    const dist = `${root}/dist`;
    
    // assemble globs for each plugin
    let globs = [];
    plugins(function(plugin) {
        const path = `${moodleRoot}/${plugin}`;
        globs.push(`${path}/**`, `!${path}/{dev,dev/**,tests,tests/**}`);
    });

    // read files from globs and output to zip file
    gulp.src(globs, {base: `${moodleRoot}`})
        .pipe(zip(file))
        .pipe(gulp.dest(dist));

    return done();
});

gulp.task('vendor', function(done) {
    let css = [], js = [];

    for (let v in config.moodle.vendor) {
        var vv = config.moodle.vendor[v];
        var ext = vv.split('.').pop();
        var path = `${root}/${vv}`;

        if (ext == 'js') js.push(path);
        if (ext == 'css') css.push(path);
    }

    gulp.src(js)
        .pipe(concat('vendor.js'))
        .pipe(gulp.dest(`${moodleRoot}`));

    gulp.src(css)
        .pipe(concat('vendor.css'))
        .pipe(gulp.dest(`${moodleRoot}`));
});

gulp.task('watch', ['build'], function() {
    // assemble globs to watch
    let globs = [];
    plugins(function(plugin) {
        const path = `${moodleRoot}/${plugin}/dev`;
        globs.push(`${path}/**/*`);
    });

    // watch the globs
    gulp.watch(globs, {interval: 1500}, function() {
        gulp.start('build');
    });
});

/**
 * Runs callback function on each plugin specified in package.json
 * @param {Function} callback 
 */
function plugins(callback) {
    for (let p in config.moodle.plugins) {
        if (typeof(callback) === "function") {
            callback(config.moodle.plugins[p]);
        }
    }
}