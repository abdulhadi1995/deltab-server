<?php

require 'vendor/autoload.php';
$client = new MongoDB\Client("mongodb+srv://deltaB:MyNameIsZee@deltab-cluster-f9vx9.mongodb.net/test?retryWrites=true&w=majority");
$db = $client->deltab_app;
$collection = $db->templates;
$document = array(
    "title" => "MongoDB",
    "description" => "database",
    "likes" => 100,
    "url" => "http://www.tutorialspoint.com/mongodb/",
    "by" => "tutorials point"
);
$tracks = $_SERVER['SERVER_ADDR'].'/var/www/html/uploads/soundtracks';
$rootPath = '/var/www/html';

$videoProcessing = new Andchir\VideoProcessing([
    'melt_path' => '/usr/bin/melt',
    'session_start' => true
]);
$soundTrack = [
    "trackName" => "ComicalBanjo",
    "path" => $tracks."/ComicalBanjo.mp3"
];

$template = [
    "template_name" => "template3",
    "profile" => "hdv_720_25p",
    "watermark" => [
        "path" => $rootPath . '/uploads/tmp/logo.png',
        "height" => 50,
        "width" => 50,
        "left" => 20,
        "top" => 20
    ],
    "main_audio" => [
        "audio_src" => $rootPath . '/uploads/tmp/audio.wav',
        'in' => 0,
        'out' => -200, // last frame audio control
        'delay' => 0
    ],
    "slides" => [
        [
            "video_src" => $rootPath . '/uploads/templates/template3/video1.mp4',
            "in_time" => 0,
            "out_time"  => '',
            "disable_audio" => true,
            "text_overlay" => [
                "text" => "This is the Text 1",
                "in_time" => 0,
                "pad" => "50x0",
                "size" => 70,
                "h_align" => "center",
                'v_align' => 'bottom',
                'family' => 'Autography',
                'slide_from' => 'bottom',
                'duration' => 50,
                'in_opacity' => 0,
                'out_opacity' => 100
            ]
        ],
        [
            "video_src" => $rootPath . '/uploads/templates/template3/video2.mp4',
            "in_time" => '',
            "out_time"  => '',
            "disable_audio" => true,
            "text_overlay" => [
                "text" => "This is the Text 1",
                "in_time" => 0,
                "pad" => "50x0",
                "size" => 80,
                "h_align" => "center",
                'v_align' => 'bottom',
                'family' => 'Autography',
                'slide_from' => 'bottom',
                'duration' => 50,
                'in_opacity' => 0,
                'out_opacity' => 100
            ]
        ]
    ]
];

$collection->insertOne($template);