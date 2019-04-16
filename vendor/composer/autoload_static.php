<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitceb05d0113108358a95d4e4720e6b944
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Stripe\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitceb05d0113108358a95d4e4720e6b944::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitceb05d0113108358a95d4e4720e6b944::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}