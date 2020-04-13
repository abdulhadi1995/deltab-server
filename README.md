Video processing class
======================

Author: [https://codecanyon.net/user/andycoder](https://codecanyon.net/user/andycoder)

Features
--------
- Cut and join videos
- Join videos with transitions
- 10 ready made transitions with options
- Customizable Wipe transitions
- Add background audio
- Add watermark
- Add text overlay with options
- Add animated text
- Run rendering on background
- Get rendering progress percent

Requirements
------------
- PHP 5.3+
- Melt installed on server
- enabled PHP functions exec, shell_exec
- PHP extensions: simplexml, libxml, json

Install
-------
- Upload files to your server
- Open ``example.php`` in your browser

Example of use
--------------

~~~
require __DIR__ . '/vendor/autoload.php';

$videoProcessing = new Andchir\VideoProcessing([
    'melt_path' => '/usr/bin/melt',
    'session_start' => true
]);

$videoProcessing
    ->setProfile('hdv_720_25p')
    ->addOption(['joinClips' => [
        $rootPath . '/uploads/tmp/Social.mp4',
        $rootPath . '/uploads/tmp/Dog.mp4',
        $rootPath . '/uploads/tmp/Swans.mp4'
    ]])
    ->setOutputVideoOptions($rootPath . '/uploads/tmp/out1.mp4');
    
$videoProcessing->render();
~~~

**Profiles:**
- atsc_1080p_25
- atsc_1080p_24
- atsc_720p_25
- atsc_720p_24
- hdv_1080_25p
- hdv_720_25p
- dv_pal
- dv_pal_wide

Install Melt on Ubuntu:
~~~
sudo apt install melt
~~~

Building Melt on Windows: 
[https://www.mltframework.org/docs/windowsbuild/](https://www.mltframework.org/docs/windowsbuild/)

All video samples: 
[http://wdevblog.net.ru/video_processing_class/uploads/tmp/samples_videos.zip](http://wdevblog.net.ru/video_processing_class/uploads/tmp/samples_videos.zip)

Generate documentation:
~~~
vendor/bin/apigen generate classes --destination docs
~~~
