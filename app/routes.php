<?php

    /*
    Path: /
    Info: deploy home
    */
    $app->get('/', function () use ($app) {

        $app->render('hello.php', array(
            'app' => $app
        ));

    })->name('home');


    /* 
    Path: /section/section-name
    Info: serve sections requested via ajax 
    */
    $app->get('/section(/:name)', function ($name = 'home') use ($app) 
    {
        if ( ! $app->request()->isAjax()) $app->halt(403);
        try {
            // Attempt to pull the file from /templates/javascript
            $app->render($app->config('sections.path') . '/' . $name . '.php', array('app' => $app));
        } catch (RuntimeException $e) {
            // Something went wrong, toss the 404 :( page
            $app->render('sections/404.php', array('app' => $app));
        }
    })->name('section');

    /* 
    Path: /assets/(script|style)/file-name
    Info: serve scripts and styles (as injecting app config into JS or CSS might be necessary)
    */
    $app->get('/assets/:type/:file', function ($type = false, $file = '') use ($app) 
    {
        try {
            switch ($type) {
                
                case 'script':
                    $app->response()->header('Content-type', 'text/javascript');
                    $app->render($app->config('scripts.path') . '/' . $file . '.js', array('app'=>$app));
                    break;

                case 'style':
                    $app->response()->header('Content-type', 'text/css');
                    $app->render($app->config('styles.path') . '/' . $file . '.css', array('app' => $app));
                    break;

                default:
                    throw new Exception("Unable to serve asset", 1);
            }
        } catch (RuntimeException $e) {
            $app->halt(404);
        }
    })->name('asset');