<?php
use Silex\Application;
use Assetic\Filter\LessFilter;
use Assetic\AssetManager;
use Assetic\FilterManager;
use Assetic\Asset\FileAsset;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\AssetCache;
use Assetic\Asset\GlobAsset;
use SilexAssetic\AsseticExtension;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application;

$app['debug'] = true;

$app->register(new AsseticExtension, [
    'assetic.path_to_web' => __DIR__,
    'assetic.filters' => $app->protect(function (FilterManager $fm) {
        $less = new LessFilter('/usr/bin/node', ['/usr/lib/node_modules']);
        $less->setCompress(true);

        $fm->set('less', $less);
    }),
    'assetic.assets' => $app->protect(function (AssetManager $am, FilterManager $fm) {
        $am->set('style', new FileAsset(__DIR__ . '/../app/styles/todo.less', [$fm->get('less')]));

        $am->set('scripts', new AssetCollection([
            new FileAsset(__DIR__ . '/../app/vendor/jquery/jquery.min.js'),
            new FileAsset(__DIR__ . '/../app/vendor/angular/angular.js'),
            new GlobAsset([
                __DIR__ . '/../app/vendor/bootstrap/js/bootstrap-tooltip.js',
                __DIR__ . '/../app/vendor/bootstrap/js/*.js',
            ]),
            new GlobAsset([__DIR__ . '/../app/scripts/**/*.js']),
        ]));

        $am->get('style')->setTargetPath('todo.css');
        $am->get('scripts')->setTargetPath('todo.js');
    })
]);

$app->get('/', function () use ($app) {
    return file_get_contents(__DIR__ . '/../app/views/todo.html');
});

$app->run();
