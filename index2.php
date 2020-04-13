<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';
$rootPath = __DIR__;

$videoProcessing = new Andchir\VideoProcessing([
    'melt_path' => '/usr/bin/melt',
    'session_start' => true
]);

$clipProperties = $videoProcessing->getClipProperties($rootPath . '/uploads/tmp/video2.mp4');
echo "Duration (frames): {$clipProperties['length']}";

$template = [
    "template_name" => "template1",
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
            "video_src" => $rootPath . '/uploads/tmp/video2.mp4',
            "in_time" => 0,
            "out_time"  => '',
            "disable_audio" => true,
            "text_overlay" => [
                "text" => "This is the Text 1",
                "in_time" => 0,
                "pad" => "50x0",
                "size" => 80,
                "h_align" => "center",
                'v_align' => 'center',
                'family' => 'Candice',
                'slide_from' => 'bottom',
                'duration' => 50,
                'in_opacity' => 0,
                'out_opacity' => 100
            ]
        ],
        [
            "video_src" => $rootPath . '/uploads/tmp/video3.mp4',
            "in_time" => '',
            "out_time"  => '',
            "disable_audio" => true,
            "text_overlay" => [
                "text" => "This is the Text 1",
                "in_time" => 0,
                "pad" => "50x0",
                "size" => 80,
                "h_align" => "center",
                'v_align' => 'center',
                'family' => 'Candice',
                'slide_from' => 'right',
                'duration' => 50,
                'in_opacity' => 0,
                'out_opacity' => 100
            ]
        ]
    ]
];

$videoProcessing->setProfile($template["profile"]);
$videoProcessing->addWatermark($template["watermark"]["path"], false, $template["watermark"]);
foreach ($template["slides"] as $slide) {
    $clipProperties = $videoProcessing->getClipProperties($slide["video_src"]);
    // Adjusting Main Audio Duration
    $template["main_audio"]["out"] += intval($clipProperties['length']);

    $videoProcessing->addOption([
        'inputSource' => [
            $slide["video_src"],
            [
                "in" => $slide["in_time"],
                "out" => (empty($slide["out_time"])) ? $clipProperties['length'] : $slide["out_time"]
            ]
        ]
    ]);

    $videoProcessing->addTextOverlay($slide["text_overlay"]["text"], false, [
        'in' => $slide["text_overlay"]["in_time"],
        'pad' => $slide["text_overlay"]["pad"],
        'size' => $slide["text_overlay"]["size"],
        'halign' => $slide["text_overlay"]["h_align"],
        'valign' => $slide["text_overlay"]["v_align"],
        'family' => $slide["text_overlay"]["family"],
        'slideFrom' => $slide["text_overlay"]["slide_from"],
        'duration' => $slide["text_overlay"]["duration"],
        'inOpacity' => $slide["text_overlay"]["in_opacity"],
        'outOpacity' => $slide["text_overlay"]["out_opacity"]
    ]);
    $videoProcessing->disableAudio();
}
$videoProcessing->addBackgroundAudio($template["main_audio"]["audio_src"], [
    'in' => $template["main_audio"]["in"],
    'out' => $template["main_audio"]["out"],
    'delay' => $template["main_audio"]["delay"]
]);
//$videoProcessing->addBackgroundAudio($rootPath . '/uploads/tmp/audio.wav', ['in' => 1, 'out' => '', 'delay' => 0]);
$videoProcessing->setOutputVideoOptions($rootPath . '/uploads/tmp/out_check3.mp4');
$videoProcessing->render();

/*$videoProcessing
    ->setProfile('hdv_720_25p')
    ->addWatermark($rootPath . '/uploads/tmp/logo.png', true, [
        'width' => 50,
        'height' => 50,
        'left' => 20,
        'top' => 20
    ])
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/video4.mp4', ['in' => 0, 'out' => 125]
    ]])
    ->addTextOverlay('This is my best video', false, [
        'in'  => 0,
        'pad' => '50x0',
        'size' => 80,
        'halign' => 'center',
        'valign' => 'center',
        'family' => 'Ubuntu',
        'slideFrom' => 'bottom',
        'duration' => 50,
        'inOpacity' => 0,
        'outOpacity' => 100
    ])
    ->disableAudio()
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/video2.mp4', ['in' => 50, 'out' => 200]
    ]])
    ->addReadyMadeTransition('shiftLeftIn', 25)
    ->addBackgroundAudio($rootPath . '/uploads/tmp/audio.wav', ['in' => 0, 'out' => 150, 'delay' => 0])
    ->setOutputVideoOptions($rootPath . '/uploads/tmp/out_video4.mp4')
    ->render();*/

