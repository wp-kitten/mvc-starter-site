const gulp = require( 'gulp' );
const sass = require( 'gulp-sass' );
const del = require( 'del' );
const sourcemaps = require( 'gulp-sourcemaps' );
//#! see https://github.com/postcss/autoprefixer#options
//#! @see https://github.com/postcss/autoprefixer#options
const autoprefixer = require( 'gulp-autoprefixer' );
const autoprefixerOptions = {
    browsers: ['last 2 versions', '> 5%', 'Firefox ESR']
};

/*
 * Private tasks
 */
gulp.task( '__clean', () => {
    return del( [
        'public/res/css/**/*.css',
    ] );
} );

gulp.task( '__styles', () => {
    return gulp.src( 'public/res/scss/**/*.scss' )
        .pipe( sourcemaps.init() )
        .pipe( autoprefixer() )
        .pipe( sass( { outputStyle: 'compressed' } ).on( 'error', sass.logError ) )
        .pipe( sourcemaps.write( '.' ) )
        .pipe( gulp.dest( './public/res/css/' ) );
} );

/*
 * Public tasks
 */
gulp.task( 'build', gulp.series( ['__clean', '__styles'] ) );

gulp.task( 'watch', () => {
    gulp.watch( 'public/res/scss/**/*.scss', (done) => {
        gulp.series( ['__clean', '__styles'] )( done );
    } );
} );
