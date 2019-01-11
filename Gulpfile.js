var jsfile = [
	'vendor/components/jquery/jquery.js',
	'vendor/components/jqueryui/jquery-ui.js',
	'vendor/timrwood/moment/moment.js',
	'vendor/ivaynberg/select2/dist/js/select2.full.min.js',
	'vendor/twbs/bootstrap/dist/js/bootstrap.bundle.js',
	'vendor/components/jQote2/jquery.jqote2.js',
	'vendor/components/toastr/toastr.js',
	'assets/js/plugins/jquery.getData.js',
	'assets/js/plugins/jquery.ba-dotimeout.min.js',
	'assets/js/plugins/jquery.ba-bbq.js',
	'assets/js/plugins/jquery.highlight.js',
];


var styleFiles = [

	{
		'file': './assets/scss/styles.scss',
		'path': './public/css/',
	}

];


var javascriptFiles = [
	{
		'files': jsfile,
		'path': './public/js/',
		'filename': 'libraries.js'
	}
];


const sass = require('gulp-sass');
const concat = require('gulp-concat');
const rename = require('gulp-rename');
const merge = require('merge-stream');



var gitCommitMessage = false;


var build = false;

const gulp = require('gulp');
require("time-require");
const duration = require('gulp-duration');
//const mod = gulp.submodule('mod');


gulp.task('scss', function(done) {


	if( process.argv.indexOf("--build") != -1 ) {
		build = true;
	}
	if( process.argv.indexOf("-b") != -1 ) {
		build = true;
	}


	if( build ) {
		var autoprefixer = (typeof autoprefixer !== 'undefined') ? autoprefixer : require('gulp-autoprefixer');
		var cleanCss = (typeof cleanCss !== 'undefined') ? cleanCss : require('gulp-clean-css');
		var sourcemaps = (typeof sourcemaps !== 'undefined') ? sourcemaps : require('gulp-sourcemaps');


	}

	var tasks = styleFiles.map(function(element) {

		var timer = duration(element.file);

			return gulp.src(element.file,element.src_opt)
			//
			.pipe(sass({
				outputStyle: 'compressed', includePaths: ['./']
			}))

			.pipe(gulp.dest(element.path))

			.pipe(autoprefixer({
				browsers: ['last 2 versions'], cascade: false
			}))

			.pipe(sourcemaps.init())
			.pipe(cleanCss({
				inline: ['local'], specialComments: false, //processImport: false,
			}))
			.pipe(sourcemaps.write("."))

			.pipe(timer)
			.pipe(gulp.dest(element.path))

	});
	return merge(tasks);

});

gulp.task('javascript', function(done) {

	if( process.argv.indexOf("--build") != -1 ) {
		build = true;
	}
	if( process.argv.indexOf("-b") != -1 ) {
		build = true;
	}


	if( build ) {
		var sourcemaps = (typeof sourcemaps !== 'undefined') ? sourcemaps : require('gulp-sourcemaps');
		var uglify = (typeof uglify !== 'undefined') ? uglify : require('gulp-uglify');
	}

	var uglify_options = {
		//preserveComments: 'license',
		compress: false,

	};


	//build = false;
	var tasks = javascriptFiles.map(function(element) {

		var timer = duration(element.path + "/" + element.filename);

		if( build ) {
			return gulp.src(element.files)

			.pipe(concat(element.filename, {newLine: ';'}))
			.pipe(rename(element.filename))
			.pipe(sourcemaps.init())
			.pipe(uglify(uglify_options))
			.pipe(sourcemaps.write("."))
			.pipe(timer)
			.pipe(gulp.dest(element.path));
		} else {
			return gulp.src(element.files)

			.pipe(concat(element.filename, {newLine: ';'}))
			.pipe(rename(element.filename))
			.pipe(timer)
			.pipe(gulp.dest(element.path));
		}
	});
	return merge(tasks);


});


gulp.task('copyfonts', function(done) {
	gulp.src('./vendor/FortAwesome/Font-Awesome/web-fonts-with-css/webfonts/*')
		.pipe(gulp.dest('./public/fonts/'));
	done();
});
gulp.task('copyjqueryuiimages', function(done) {
	gulp.src('./vendor/components/jqueryui/themes/base/images/*')
		.pipe(gulp.dest('./public/images/'));
	done();
});




gulp.task('set-build', function(done) {
	build = true;
	if( process.argv.indexOf("--dev") != -1 || process.argv.indexOf("-d") != -1 ) {
		build = false;
	}
	done();
});



gulp.task('build', gulp.series('set-build', gulp.parallel([
	'scss',
	'javascript',
	'copyfonts',
	'copyjqueryuiimages'
]), function(done) {
	done();
}));



gulp.task('git-commit', function(done) {
	gitCommitMessage = (typeof gitCommitMessage !== 'undefined' && gitCommitMessage != "") ? gitCommitMessage : "gulp commit";

	var d = new Date();
	var prefix = d.getFullYear() + "-" + ("0" + (d.getMonth() + 1)).slice(-2) + "-" + ("0" + d.getDate()).slice(-2) + " " + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2) + ":" + ("0" + d.getSeconds()).slice(-2);


	gitCommitMessage = prefix + "\n" + gitCommitMessage;

	git = (typeof git !== 'undefined') ? git : require('gulp-git');

	var timer = duration('git-commit');
	return gulp.src('./')
	.pipe(git.commit(gitCommitMessage))
	.pipe(timer);


});

gulp.task('git-push', function(done) {

	git = (typeof git !== 'undefined') ? git : require('gulp-git');

	git.push( 'github', function(err) {
		if( err ) {
			throw err;
		} else {
			done();
		}
	});



});
gulp.task('git-diff', function(done) {
	git = (typeof git !== 'undefined') ? git : require('gulp-git');
	git.exec({args: ' diff --stat'}, function(err, stdout) {
		gitCommitMessage = stdout
		if( err ) {
			throw err;
		}
		done();
	});
});


gulp.task('js', gulp.series('javascript', function(done) {
	done();
}));
gulp.task('css', gulp.series('scss','copyfonts','copyjqueryuiimages', function(done) {
	done();
}));



gulp.task('deploy', gulp.series('build','git-diff','git-commit', 'git-push', function(done) {
	done();
}));
