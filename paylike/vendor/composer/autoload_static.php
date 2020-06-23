<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit77c2df5e09531af29aa2b2cfaae9100b
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Paylike\\Tests\\' => 14,
            'Paylike\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Paylike\\Tests\\' => 
        array (
            0 => __DIR__ . '/..' . '/paylike/php-api/tests',
        ),
        'Paylike\\' => 
        array (
            0 => __DIR__ . '/..' . '/paylike/php-api/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit77c2df5e09531af29aa2b2cfaae9100b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit77c2df5e09531af29aa2b2cfaae9100b::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
