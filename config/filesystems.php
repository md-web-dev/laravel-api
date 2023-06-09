<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    "default" => env("FILESYSTEM_DRIVER", "local"),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    "cloud" => env("FILESYSTEM_CLOUD", "s3"),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    "disks" => [

        "local" => [
            "driver"      => "local",
            "root"        => storage_path("app/public"),
            "url"        => env("APP_URL") . "/storage",
            "permissions" => [
                "file" => [
                    "public"  => 0664,
                    "private" => 0600,
                ],
                "dir"  => [
                    "public"  => 0775,
                    "private" => 0700,
                ],
            ],
        ],

        "public" => [
            "driver"     => "local",
            "root"       => storage_path("app/public"),
            "url"        => env("APP_URL") . "/storage",
            "visibility" => "public",
        ],

        "s3" => [
            "driver" => "s3",
            "key"    => env("AWS_ACCESS_KEY_ID"),
            "secret" => env("AWS_SECRET_ACCESS_KEY"),
            "region" => env("AWS_DEFAULT_REGION"),
            "bucket" => env("AWS_BUCKET"),
            "url"    => env("AWS_URL"),
        ],

        "s3-apparel" => [
            "driver" => "s3",
            "key"    => env("AWS_ACCESS_KEY_ID"),
            "secret" => env("AWS_SECRET_ACCESS_KEY"),
            "region" => env("AWS_DEFAULT_REGION"),
            "bucket" => env("AWS_APPAREL_BUCKET"),
            "url"    => env("AWS_APPAREL_URL"),
        ],

        "s3-soundblock" => [
            "driver" => "s3",
            "key"    => env("AWS_ACCESS_KEY_ID"),
            "secret" => env("AWS_SECRET_ACCESS_KEY"),
            "region" => env("AWS_DEFAULT_REGION"),
            "bucket" => env("AWS_SOUNDBLOCK_BUCKET"),
            "url"    => env("AWS_SOUNDBLOCK_URL"),
        ],

        "s3-account" => [
            "driver" => "s3",
            "key"    => env("AWS_ACCESS_KEY_ID"),
            "secret" => env("AWS_SECRET_ACCESS_KEY"),
            "region" => env("AWS_DEFAULT_REGION"),
            "bucket" => env("AWS_ACCOUNT_BUCKET"),
            "url"    => env("AWS_ACCOUNT_URL"),
        ],

        "s3-core" => [
            "driver" => "s3",
            "key"    => env("AWS_ACCESS_KEY_ID"),
            "secret" => env("AWS_SECRET_ACCESS_KEY"),
            "region" => env("AWS_DEFAULT_REGION"),
            "bucket" => env("AWS_CORE_BUCKET"),
            "url"    => env("AWS_CORE_URL"),
        ],

        "reports-ftp" => [
            "driver"    => "ftp",
            'host'      => env("REPORTS_MERLIN_HOST"),
            'username'  => env("REPORTS_MERLIN_USERNAME"),
            'password'  => env("REPORTS_MERLIN_PASSWORD"),
            'port'      => env("REPORTS_MERLIN_PORT",21),
            'root'      => env("REPORTS_MERLIN_ROOT",""),
//            'passive'   => env("REPORTS_MERLIN_PASSIVE",true),
            'ssl'       => env("REPORTS_MERLIN_SSL",false),
//            'timeout'   => env("REPORTS_MERLIN_TIMEOUT",30),
        ],

    ],
];
