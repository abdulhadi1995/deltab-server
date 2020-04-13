<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

$videoProcessing = new Andchir\VideoProcessing([
    'session_start' => true
]);
$rootPath = __DIR__;

// Get rendering percent
if (!empty($_GET['test_get_percent'])) {
    $percent = $videoProcessing->getRenderingPercent();
    if (is_null($percent)) {
        $percent = 100;
    }
    echo json_encode(['percent' => $percent]);
    exit;
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    <title>Video processing with Melt - PHP Class</title>
</head>
<body>

    <main role="main" class="container pt-4">
        <h1>Video processing with Melt</h1>
        <div class="mb-2">
            <a href="https://github.com/mltframework/mlt" target="_blank">https://www.mltframework.org</a>
        </div>
        <div class="mb-2">
            <a href="https://codecanyon.net/item/php-class-for-video-processing/22631954" target="_blank">Buy on Codecanyon</a>
        </div>
        <div>
            PHP Class
        </div>
        <hr>

        <pre class="p-2 bg-secondary text-light">
$videoProcessing = new Andchir\VideoProcessing(['melt_path' => '/usr/bin/melt']);</pre>

        <h3>Get Melt data</h3>
        <pre class="p-2 bg-secondary text-light">
$videoProcessing->addOption(['version' => []]);
echo $videoProcessing->getOutput();</pre>

        <pre class="p-2 bg-secondary text-light">
$videoProcessing->addOption(['query' => 'formats']);
echo $videoProcessing->getOutput();</pre>

        <pre class="p-2 bg-secondary text-light">
$videoProcessing->addOption(['query' => 'transitions']);
echo $videoProcessing->getOutput();</pre>

        <h3>Media source properties</h3>
        <pre class="p-2 bg-secondary text-light">
<?php
echo <<<'EOT'
$clipProperties = $videoProcessing->getClipProperties($rootPath . '/uploads/tmp/Dog.mp4');

echo "Duration (frames): {$clipProperties['length']}";
echo "&lt;br&gt;Frame width: {$clipProperties['meta.media.0.codec.width']}";
echo "&lt;br&gt;Frame height: {$clipProperties['meta.media.0.codec.height']}";
echo "&lt;br&gt;FPS: {$clipProperties['meta.media.0.codec.frame_rate']}";
EOT;
?>
        </pre>

        <h3>Join clips</h3>
        <pre class="p-2 bg-secondary text-light">
<?php
echo <<<'EOT'
$videoProcessing
    ->setProfile('hdv_720_25p')
    ->addOption(['joinClips' => [
        $rootPath . '/uploads/tmp/Social.mp4',
        $rootPath . '/uploads/tmp/Dog.mp4',
        $rootPath . '/uploads/tmp/Swans.mp4'
    ]])
    ->setOutputVideoOptions($rootPath . '/uploads/tmp/out1.mp4');
EOT;
?>
        </pre>

        <div class="my-3">
            <video src="uploads/tmp/out1.mp4" width="640" height="360" controls></video>
        </div>

        <h3>Black color and fade transition</h3>
        <pre class="p-2 bg-secondary text-light">
<?php
echo <<<'EOT'
$videoProcessing
    ->setProfile('hdv_720_25p')
    ->addOption(['inputSource' => [
        'colour:black', ['out' => 24],
        $rootPath . '/uploads/tmp/Dog.mp4'
    ]])
    ->addOption(['mix' => 25])
    ->addOption(['mixer' => 'luma'])
    ->addOption(['inputSource' => [
        'colour:black', ['out' => 24]
    ]])
    ->addOption(['mix' => 25])
    ->addOption(['mixer' => 'luma'])
    ->setOutputVideoOptions($rootPath . '/uploads/tmp/out2.mp4');
EOT;
?>
        </pre>

        <div class="my-3">
            <video src="uploads/tmp/out2.mp4" width="640" height="360" controls></video>
        </div>

        <h3>Join clips with transition</h3>

        <pre class="p-2 bg-secondary text-light">
<?php
echo <<<'EOT'
$videoProcessing
    ->setProfile('hdv_720_25p')
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Swans.mp4',
        $rootPath . '/uploads/tmp/Dog.mp4'
    ]])
    ->addOption(['mix' => 25])
    ->addOption(['mixer' => 'luma'])
    ->setOutputVideoOptions($rootPath . '/uploads/tmp/out3.mp4');
EOT;
?>
        </pre>

        <div class="my-3">
            <video src="uploads/tmp/out3.mp4" width="640" height="360" controls></video>
        </div>

        <h3>Cut clips and join with transition</h3>
        <pre class="p-2 bg-secondary text-light">
<?php
echo <<<'EOT'
$videoProcessing
    ->setProfile('hdv_720_25p')
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Social.mp4', ['in' => 200, 'out' => 275]
    ]])
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Dog.mp4', ['in' => 50, 'out' => 125]
    ]])
    ->addReadyMadeTransition('fade', 25)
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Swans.mp4', ['in' => 50, 'out' => 125]
    ]])
    ->addReadyMadeTransition('shiftRightIn', 25, [
        'width' => 1280,
        'height' => 720
    ])
    ->setOutputVideoOptions($rootPath . '/uploads/tmp/out4.mp4');
EOT;
?>
        </pre>

        <div class="my-3">
            <video src="uploads/tmp/out4.mp4" width="640" height="360" controls></video>
        </div>

        <hr>

        <pre class="p-2 bg-secondary text-light">
<?php
echo <<<'EOT'
$videoProcessing
    ->setProfile('hdv_720_25p')
    ->setOutputFormat('webm')
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Dog.mp4', ['in' => 50, 'out' => 125]
    ]])
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Swans.mp4', ['in' => 50, 'out' => 125]
    ]])
    ->addReadyMadeTransition('shiftLeftIn', 25, [
        'inOpacity' => 0
    ])
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Social.mp4', ['in' => 50, 'out' => 125]
    ]])
    ->addReadyMadeTransition('shiftRightOut', 25, [
        'outWidth' => 0,
        'outHeight' => 0
    ])
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Dog.mp4', ['in' => 50, 'out' => 125]
    ]])
    ->addReadyMadeTransition('shiftTopIn', 25)
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Swans.mp4', ['in' => 50, 'out' => 125]
    ]])
    ->addReadyMadeTransition('shiftBottomOut', 25, [
        'outWidth' => 0,
        'outHeight' => 0
    ])
    ->setOutputVideoOptions($rootPath . '/uploads/tmp/out5.webm');
EOT;
?>
        </pre>

        <div class="my-3">
            <video src="uploads/tmp/out5.webm" width="640" height="360" controls></video>
        </div>

        <h3>Wipe transition</h3>

        <pre class="p-2 bg-secondary text-light">
<?php
echo <<<'EOT'
$videoProcessing
    ->setProfile('hdv_720_25p')
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Swans.mp4', ['in' => 50, 'out' => 125]
    ]])
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Dog.mp4', ['in' => 50, 'out' => 125]
    ]])
    ->addReadyMadeTransition('wipeIn', 37, [
        'softness' => '0.1'
    ])
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Social.mp4', ['in' => 50, 'out' => 125]
    ]])
    ->addReadyMadeTransition('wipeIn', 37, [
        'wipeName' => 'clock.pgm',
        'softness' => '0.05'
    ])
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Swans.mp4', ['in' => 50, 'out' => 125]
    ]])
    ->addReadyMadeTransition('wipeOut', 37, [
        'wipeName' => 'clock.pgm',
        'softness' => '0.05'
    ])
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Dog.mp4', ['in' => 50, 'out' => 125]
    ]])
    ->addReadyMadeTransition('wipeIn', 37, [
        'wipeName' => 'spiral.pgm',
        'softness' => '0.05'
    ])
    ->setOutputVideoOptions($rootPath . '/uploads/tmp/out6.mp4');
EOT;
?>
        </pre>

        <div class="my-3">
            <video src="uploads/tmp/out6.mp4" width="640" height="360" controls></video>
        </div>

        <h3>Add background audio with delay</h3>
        <pre class="p-2 bg-secondary text-light">
<?php
echo <<<'EOT'
$videoProcessing
    ->setProfile('hdv_720_25p')
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Swans.mp4', ['in' => 50, 'out' => 125]
    ]])
    ->disableAudio()
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Dog.mp4', ['in' => 50, 'out' => 200]
    ]])
    ->addReadyMadeTransition('shiftLeftIn', 25)
    ->addBackgroundAudio($rootPath . '/uploads/tmp/Reformat.mp3', ['in' => 0, 'out' => 150, 'delay' => 50])
    ->setOutputVideoOptions($rootPath . '/uploads/tmp/out7.mp4');
EOT;
?>
        </pre>

        <div class="my-3">
            <video src="uploads/tmp/out7.mp4" width="640" height="360" controls></video>
        </div>

        <h3>Add watermark</h3>
        <pre class="p-2 bg-secondary text-light">
<?php
echo <<<'EOT'
$videoProcessing
    ->setProfile('hdv_720_25p')
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Swans.mp4', ['in' => 50, 'out' => 125]
    ]])
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Dog.mp4', ['in' => 50, 'out' => 200]
    ]])
    ->addWatermark($rootPath . '/uploads/tmp/SampleLogo.png', false, [
        'distort' => 1
    ])
    ->addReadyMadeTransition('shiftLeftIn', 25)
    ->setOutputVideoOptions($rootPath . '/uploads/tmp/out8.mp4');
EOT;
?>
        </pre>

        <div class="my-3">
            <video src="uploads/tmp/out8.mp4" width="640" height="360" controls></video>
        </div>

        <hr>

        <pre class="p-2 bg-secondary text-light">
<?php
echo <<<'EOT'
$videoProcessing
    ->setProfile('hdv_720_25p')
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Swans.mp4', ['in' => 50, 'out' => 125]
    ]])
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Dog.mp4', ['in' => 50, 'out' => 200]
    ]])
    ->addReadyMadeTransition('shiftLeftIn', 25)
    ->addWatermark($rootPath . '/uploads/tmp/SampleLogo.png', true, [
        'width' => 300,
        'height' => 300,
        'left' => 0,
        'top' => 450
    ])
    ->setOutputVideoOptions($rootPath . '/uploads/tmp/out9.mp4');
EOT;
?>
        </pre>

        <div class="my-3">
            <video src="uploads/tmp/out9.mp4" width="640" height="360" controls></video>
        </div>

        <h3>Add text overlay</h3>
        <pre class="p-2 bg-secondary text-light">
<?php
echo <<<'EOT'
$videoProcessing
    ->setProfile('hdv_720_25p')
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Swans.mp4', ['out' => 120]
    ]])
    ->addTextOverlay('This is my best video', true, [
        'fgcolour' => '#004fed',
        'olcolour' => '#fff200',
        'outline' => 3,
        'pad' => '50x0',
        'size' => 80,
        'weight' => 700,
        'style' => 'italic',
        'halign' => 'center',
        'valign' => 'top',
        'family' => 'Ubuntu'
    ])
    ->setOutputVideoOptions($rootPath . '/uploads/tmp/out10.mp4')
EOT;
?>
        </pre>

        <div class="my-3">
            <video src="uploads/tmp/out10.mp4" width="640" height="360" controls></video>
        </div>

        <h3>Animated text</h3>
        <pre class="p-2 bg-secondary text-light">
<?php
echo <<<'EOT'
$videoProcessing
    ->setProfile('hdv_720_25p')
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Swans.mp4', ['out' => 120]
    ]])
    ->addTextOverlay('This is my best video', true, [
        'pad' => '50x0',
        'size' => 80,
        'halign' => 'center',
        'valign' => 'top',
        'family' => 'Ubuntu',
        'slideFrom' => 'bottom',
        'duration' => 50,
        'inOpacity' => 0,
        'outOpacity' => 100
    ])
    ->setOutputVideoOptions($rootPath . '/uploads/tmp/out11.mp4');
EOT;
?>
        </pre>

        <div class="my-3">
            <video src="uploads/tmp/out11.mp4" width="640" height="360" controls></video>
        </div>

        <a name="image-slideshow"></a>
        <h3>Slide show from static images + audio fade out</h3>

        <pre class="p-2 bg-secondary text-light">
<?php
echo <<<'EOT'
$videoProcessing
    ->setProfile('hdv_720_25p')
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/picture1.jpg', ['out' => 100]
    ]])
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/picture2.jpg', ['out' => 100]
    ]])
    ->addReadyMadeTransition('wipeIn', 25, [
        'wipeName' => 'cloud.pgm'
    ])
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/picture3.jpg', ['out' => 100]
    ]])
    ->addReadyMadeTransition('wipeIn', 25, [
        'wipeName' => 'burst.pgm'
    ])
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/picture4.jpg', ['out' => 100]
    ]])
    ->addReadyMadeTransition('wipeIn', 25, [
        'wipeName' => 'radial-bars.pgm'
    ])
    ->addBackgroundAudio($rootPath . '/uploads/tmp/Reformat.mp3', ['out' => 100 * 4 - (24 * 3)])
    ->addOption(['filter' => [
        'volume',
        ['gain' => 1, 'end' => 0],
        ['in' => 200, 'out' => 100 * 4 - (24 * 3)]
    ]])
    ->setOutputVideoOptions($rootPath . '/uploads/tmp/out12.mp4');
EOT;
?>
        </pre>

        <div class="my-3">
            <video src="uploads/tmp/out12.mp4" width="640" height="360" controls></video>
        </div>

        <h3>Rendering</h3>
        <pre class="p-2 bg-secondary text-light">
<?php
echo <<<'EOT'
$videoProcessing
    ->setProfile('hdv_720_25p')
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Swans.mp4', ['out' => 120]
    ]])
    ->disableAudio()
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Dog.mp4', ['out' => 120]
    ]])
    ->addReadyMadeTransition('shiftLeftIn', 25)
    ->addBackgroundAudio($rootPath . '/uploads/tmp/Reformat.mp3', ['out' => 215])
    ->setOutputVideoOptions($rootPath . '/uploads/tmp/out.mp4');

// Start rendering in background
$progressLogPath = $videoProcessing->render();
EOT;

// Testing
$videoProcessing
    ->setProfile('hdv_720_25p')
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Swans.mp4', ['out' => 120]
    ]])
    ->disableAudio()
    ->addOption(['inputSource' => [
        $rootPath . '/uploads/tmp/Dog.mp4', ['out' => 120]
    ]])
    ->addReadyMadeTransition('shiftLeftIn', 25)
    ->addBackgroundAudio($rootPath . '/uploads/tmp/Reformat.mp3', ['out' => 215])
    ->setOutputVideoOptions($rootPath . '/uploads/tmp/out.mp4');

// Testing
$updateRenderingPercent = false;
if (!empty($_POST['test_render'])) {
    $percent = $videoProcessing->getRenderingPercent();
    $updateRenderingPercent = true;
    if (is_null($percent) && empty($videoProcessing->getPidArr())) {
        $videoProcessing->render();
    }
}
?>
        </pre>

        <h3>Rendering progress</h3>
    <pre class="p-2 bg-secondary text-light">
<?php
echo <<<'EOT'
// Optional arguments: log file path, PID
// Otherwise it is taken from the session
$percent = $videoProcessing->getRenderingPercent();
EOT;
?>
    </pre>

        <hr>

        <h3>Test rendering</h3>

        <a name="render_form"></a>
        <div class="mb-3">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>#render_form" method="post">
                <button class="btn btn-primary btn-lg" type="submit" name="test_render" id="renderingButton" value="1"<?php if ($updateRenderingPercent): ?> disabled<?php endif; ?>>
                    Start rendering
                </button>
            </form>
        </div>

        <?php if ($updateRenderingPercent): ?>
            <div class="alert alert-info">
                Rendering progress: <span id="renderingPercent">0</span>%
            </div>
            <script>
                var timer,
                    onRenderCompleted = function() {
                        document.getElementById('renderingButton').removeAttribute('disabled');
                        document.getElementById('renderingResult').style.display = 'block';
                        var video = document.getElementById('renderingResult').querySelector('video');
                        setTimeout(function() {
                            video.src = 'uploads/tmp/out.mp4';
                        }, 2000);
                    },
                    getPercent = function () {
                    clearTimeout(timer);
                    var request = new XMLHttpRequest();
                    request.open('GET', '<?php echo $_SERVER['PHP_SELF']; ?>?test_get_percent=1', true);
                    request.onload = function() {
                        if (request.status >= 200 && request.status < 400) {
                            var data = JSON.parse(request.responseText);
                            if (typeof data.percent !== 'undefined') {
                                document.getElementById('renderingPercent').textContent = data.percent;
                                if (data.percent < 100) {
                                    timer = setTimeout(getPercent, 2000);
                                } else {
                                    onRenderCompleted();
                                }
                            }
                        }
                    };
                    request.send();
                };
                timer = setTimeout(getPercent, 2000);
            </script>
        <?php endif; ?>

        <div class="my-3" id="renderingResult" style="display: none;">
            <div class="mb-2">
                <a href="uploads/tmp/out.mp4" target="_blank">Open Video</a>
            </div>
            <video src="" width="640" height="360" controls></video>
        </div>

        <hr>

        <br><br><br>

    </main>
</body>
</html>