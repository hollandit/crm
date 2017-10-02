<?php
return [
	'language' => 'ru-RU',
    'sourceLanguage' =>'ru-RU',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'cacheRbac' => [
            'class' => 'yii\caching\ApcCache',
        ],
        'formatter' => [
            'datetimeFormat' => 'php: d M H:i',
        ]
    ],
];
