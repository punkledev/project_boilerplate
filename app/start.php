<?php

require '../vendor/autoload.php';

/**
 * Instantiation and configuration
 */

    // Instantiate slimzzors
    $app = new \Slim\Slim(array(
        'mode'              => preg_match('/nyaa/isU',$_SERVER['HTTP_HOST']) ? 'development' : 'production', // this takes care of *.nyaa hosts
        'templates.path'    => '../app/views',
        'scripts.path'      => 'scripts',
        'styles.path'       => 'styles',
        'sections.path'     => 'sections',
        'wordpress.location' => '/wp/wp-admin/admin-ajax.php',
        'wordpress.cache' => dirname(__FILE__) . '/cache',
        ) 
    );

    // Development environment
    $app->configureMode('development', function () use ($app) {
        $app->config(array(
            'debug'         => true,
            'log.enable'    => true,
            )
        );
    });

    // Production environment
    $app->configureMode('production', function () use ($app) {
        $app->config(array(
            'debug'         => false,
            'log.enable'    => true,
            'log.level' => \Slim\Log::WARN,
            ));
    });

    // Instantiate wordpress bridge -- uncomment if needed
    // $app->wordpress = new WordpressBridge($app->config('wordpress.location'),$app->config('wordpress.cache'));


