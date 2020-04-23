<?php

require __DIR__ . '/vendor/autoload.php';

if(isset($argc)){
    $jobId = $argv[1];
    $logFile = $argv[2];
    $videoProcessing = new Andchir\VideoProcessing([
        'melt_path' => '/usr/bin/melt',
        'session_start' => true
    ]);

    $path = '/var/www/html/uploads/tmp/'.$logFile;
    $status = 0;
    do{
        //exec("curl http://localhost/rendering_jobs/".$logFile,$status);
        $cmd = "curl http://167.172.148.204/emitRenderingStatus/{$jobId}";
        $status = shell_exec($cmd);
    }while($status < 100);
}else{
    echo "Nathi";
}

