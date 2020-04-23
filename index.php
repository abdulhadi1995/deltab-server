<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
/*include_once 'vendor/firebase/php-jwt/src/BeforeValidException.php';
include_once 'vendor/firebase/php-jwt/src/ExpiredException.php';
include_once 'vendor/firebase/php-jwt/src/SignatureInvalidException.php';
include_once 'vendor/firebase/php-jwt/src/JWT.php';*/

require __DIR__ . '/vendor/autoload.php';
use \Firebase\JWT\JWT;
class DeltaB_Renderer
{
    private $rootPath = '/var/www/html';
    private $videoProcessing;
    private $socket;
    private $socketServer;

    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function mapUserDataToTemplate($template, $data)
    {
        $logo = $data->images;
        if(!empty($logo)) {
            $watermark = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $logo["logo"]));
            $fileName = 'wm_' . 'user_1_' . $this->generateRandomString() . '.jpeg';
            $path = $this->rootPath . '/uploads/tmp/' . $fileName;
            file_put_contents($path, $watermark);
            $template["watermark"]["path"] = $path;
        }
        $texts = $data->texts;
        $audio = $data->audio;
        $audioCustom = $data->audioCustom;
        if (!empty($texts)) {
            for ($i = 0; $i < sizeof($texts); $i++) {
                $template["slides"][$i]["text_overlay"]["text"] = $texts[$i];
            }
        }
        if(!empty($audio)) {
            $template["main_audio"]["audio_src"] = $audio;
        } elseif (!empty($audioCustom)){
            $sound = base64_decode(preg_replace('#^data:audio/\w+;base64,#i', '', $audioCustom["audio"]));
            $fileName = 'st_' . 'user_1_' . $this->generateRandomString() . '.mp3';

            file_put_contents($fileName, $sound);
            $template["main_audio"]["audio_src"] = 'http://' . $_SERVER['SERVER_ADDR'] . "/" . $fileName;
        }


        file_put_contents('temp.txt',print_r($template,true));
        return $template;
    }

    public function new_template(){
        $request = Flight::request();
        $data = $request->data;
        //Dealing with Audio
        $audio = $data->audio;
        if(!empty($audio)) {
            $sound = base64_decode(preg_replace('#^data:audio/\w+;base64,#i', '', $audio));
            $fileName = 'template_' . 'st_1_' . $this->generateRandomString() . '.mp3';
            $audio_path = $this->rootPath . '/uploads/tmp/' . $fileName;
            file_put_contents($audio_path, $sound);
        }
        //Dealing with Name
        if(!empty($data->name)) {
            $template_name = $data->name["name"];
        }
        //Dealing with font
        if(!empty($data->font)) {
            $fontRaw = array_keys($data->font);
            $fontName = $fontRaw[0];
            $font = base64_decode(preg_replace('#^data:application/\w+;base64,#i', '', $data->font[$fontName]));
            $font_fileName = $fontName . '.ttf';
            $font_path = '/usr/share/fonts/truetype/' . $font_fileName;
            file_put_contents($font_path, $font);
        }
        //Dealing with Videos and co-ordinates some heavy shits about to go down here
            $videos = $data->videos;
            $coordinates = $data->coordinates;

        //Final Template
      /*  $template_for_db = [
            "template_name" => $template_name,
            "profile" => "hdv_720_25p",
            "usage" => 0,
            "watermark" => [
                "path" => $this->rootPath . '/uploads/tmp/logo.png',
                "height" => 50,
                "width" => 50,
                "left" => 20,
                "top" => 20
            ],
            "main_audio" => [
                "audio_src" => $audio_path,
                'in' => 0,
                'out' => -200, // last frame audio control
                'delay' => 0
            ],
            "slides" => [
                [
                    "video_src" => $this->rootPath . '/uploads/templates/template3/video1.mp4',
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
                    "video_src" => $this->rootPath . '/uploads/templates/template3/video2.mp4',
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
        ];*/
        file_put_contents('templateData.txt',print_r($data->font[$fontName],true));
        Flight::json('201');
    }

    public function __construct()
    {
        $this->videoProcessing = new Andchir\VideoProcessing([
            'melt_path' => '/usr/bin/melt',
            'session_start' => true
        ]);

        try{
            $this->socket = new \HemiFrame\Lib\WebSocket\WebSocket("localhost", 3333);
            $this->socketServer = $this->socket->connect();
        }catch (Exception $err){

        }
    }

    public function home()
    {
        $request = Flight::request();
        $template_name = $request->data->template;
        $template = $this->getTemplate($template_name);
        $texts = $request->data->text;
        $t = $this->mapUserDataToTemplate($template, $texts);
        echo "<pre>";
        print_r($t);
        echo "Renderer In Position";

    }

    private function BSONToArray($cursor)
    {
        $arr = json_encode(iterator_to_array($cursor));
        return json_decode($arr, true);
    }

    public function getTemplate($template_name = "default")
    {
        $client = Flight::db();
        $templates = $client->deltab_app->templates;
        $result = $templates->findOne(
            [
                'template_name' => $template_name
            ]
        );
        if (!empty($result)) {
            return (array)$this->BSONToArray($result);
        } else {
            return [];
        }

    }

    public function getAllTemplates()
    {
        $client = Flight::db();
        $templates = $client->deltab_app->templates;
        $result = $templates->find();

        $d = iterator_to_array($result);
        if (sizeof($d) > 0) {
            echo Flight::json($d);
        } else {
            echo "Template Not Found";
        }
    }

    public function getAllSoundtracks()
    {
        $dir = realpath(__DIR__) . '/uploads/soundtracks';
        $result = [];
        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $entry = 'http://' . $_SERVER['SERVER_ADDR'] . "/media/audio/" . $entry;
                    array_push($result, $entry);
                }
            }
            closedir($handle);
        }
        Flight::json($result);
    }


    /*public function getRenderer($template=""){
       // $client = new MongoDB\Client("mongodb+srv://deltaB:MyNameIsZee@deltab-cluster-f9vx9.mongodb.net/test?retryWrites=true&w=majority");
        $client = Flight::db();
        $templates = $client->deltab_app->templates;
        $result = $templates->findOne(
            [
                'template_name' => $template
            ]
        );
        if($result){
            echo json_encode($result);
        }else{
            echo "Template Not Found";
        }

        //$bson = MongoDB\BSON\fromPHP($result);
        //echo MongoDB\BSON\toJSON($bson);
        //$db = Flight::db();
    }*/

    public function renderTemplate($template_name)
    {
        $client = Flight::db();
        if (empty($template_name)) {
            echo json_encode([
                'status' => 400,
                'error' => 'Template Name Required'
            ]);
            return;
        }

        $template = $this->getTemplate($template_name);
        if (empty($template)) {
            echo json_encode([
                'status' => 404,
                'error' => 'Template Not Found'
            ]);
            return;
        }

        $videoProcessing = $this->videoProcessing;
        $request = Flight::request();

        $data = $request->data;
        file_put_contents('newRequest.txt',print_r($data,true));
        $decoded = JWT::decode($data->token, 'weareone', array('HS256'));
        $user_id = (array) $decoded->data->id;
        $user_id = $user_id["\$oid"];
        $video_name = $data->title;
        $template = $this->mapUserDataToTemplate($template, $data);
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
            $videoProcessing->addReadyMadeTransition('shiftLeftIn', 25);
            $videoProcessing->disableAudio();
        }
        $videoProcessing->addBackgroundAudio($template["main_audio"]["audio_src"], [
            'in' => $template["main_audio"]["in"],
            'out' => $template["main_audio"]["out"],
            'delay' => $template["main_audio"]["delay"]
        ]);
        $videoslug = 'vid_' . 'user_' . $this->generateRandomString() . '.mp4';
        $outputPath = $this->rootPath . '/uploads/rendered/videos/'.$videoslug;
        $videoProcessing->setOutputVideoOptions($outputPath);

        $renderJob = $videoProcessing->render();
        $rj_collection = $client->deltab_app->rendering_jobs;
        $renderJobDB = [
            "job_id" => $renderJob[0],
            "output_path" => $videoslug,
            "status" => "pending",
            "user_id" => $user_id,
            "log_file" => $renderJob[1],
            "video_name"=> $video_name,
            "template_used"=> $template_name,
            "date_created"=> date('m/d/Y h:i:s a', time()),
            "template"=>serialize($template)
        ];

        $rj_collection->insertOne($renderJobDB);

        //exec("nohup php send_status.php ".$renderJob[0]." ".$renderJob[1] ."> /dev/null 2>/dev/null &");
        //shell_exec("curl http://localhost/test_socket/{$renderJob[0]}/$renderJob[1]");
        $this->test_socket($renderJob[0]); // Invoking the MC
        Flight::json([
            'status' => 200,
            'pid' => $renderJob[0],
            'log_file' => $log_file = str_replace("/var/www/html/uploads/tmp/",'',$renderJob[1]),
            'error' => ''
        ]);
    }

    public function renderingStatus($template_name)
    {
        echo $this->videoProcessing->getRenderingPercent();
    }

    public function getAudio($audio_name)
    {
        $dir = realpath(__DIR__) . "/uploads/soundtracks";
        $filename = $audio_name;
        $file = $dir . "/" . $filename;
        echo $file;
        $extension = "mp3";
        $mime_type = "audio/mp3, audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3";
        if (file_exists($file)) {
            header("Content-Type: audio/mp3");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: " . filesize($file));
            readfile($file);
        } else {
            header("HTTP/1.0 404 Not Found");
        }
    }
    public  function  download_video($video_name){

        $dir = realpath(__DIR__) . "/uploads/rendered/videos";
        $filename = $video_name;
        $path = $dir . "/" . $filename;
                // Process download
                if(file_exists($path)) {
                    header("Access-Control-Allow-Origin: *");
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="'.basename($path).'"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($path));
                    flush(); // Flush system output buffer
                    readfile($path);
                    die();
                } else {
                    http_response_code(404);
                    die();
                }
    }
    public function getVideo($video_name)
    {
        $dir = realpath(__DIR__) . "/uploads/rendered/videos";
        $filename = $video_name;
        $path = $dir . "/" . $filename;
        if ($fp = fopen($path, "rb")) {
            $size = filesize($path);
            $length = $size;
            $start = 0;
            $end = $size - 1;
            header('Content-type: video/mp4');
            header("Accept-Ranges: 0-$length");
            if (isset($_SERVER['HTTP_RANGE'])) {
                $c_start = $start;
                $c_end = $end;
                list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
                if (strpos($range, ',') !== false) {
                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                    header("Content-Range: bytes $start-$end/$size");
                    exit;
                }
                if ($range == '-') {
                    $c_start = $size - substr($range, 1);
                } else {
                    $range = explode('-', $range);
                    $c_start = $range[0];
                    $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
                }
                $c_end = ($c_end > $end) ? $end : $c_end;
                if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                    header("Content-Range: bytes $start-$end/$size");
                    exit;
                }
                $start = $c_start;
                $end = $c_end;
                $length = $end - $start + 1;
                fseek($fp, $start);
                header('HTTP/1.1 206 Partial Content');
            }
            header("Content-Range: bytes $start-$end/$size");
            header("Content-Length: ".$length);
            $buffer = 1024 * 8;
            while(!feof($fp) && ($p = ftell($fp)) <= $end) {
                if ($p + $buffer > $end) {
                    $buffer = $end - $p + 1;
                }
                set_time_limit(0);
                echo fread($fp, $buffer);
                flush();
            }
            fclose($fp);
            exit();
        } else {
            die('file not found');
        }
    }
    public function getTemplatePreview($template)
    {
        $dir = realpath(__DIR__) . "/uploads/templates/".$template.'/preview.mp4';
        //$filename = $template;
        $path = $dir;
        if ($fp = fopen($path, "rb")) {
            $size = filesize($path);
            $length = $size;
            $start = 0;
            $end = $size - 1;
            header('Content-type: video/mp4');
            header("Accept-Ranges: 0-$length");
            if (isset($_SERVER['HTTP_RANGE'])) {
                $c_start = $start;
                $c_end = $end;
                list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
                if (strpos($range, ',') !== false) {
                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                    header("Content-Range: bytes $start-$end/$size");
                    exit;
                }
                if ($range == '-') {
                    $c_start = $size - substr($range, 1);
                } else {
                    $range = explode('-', $range);
                    $c_start = $range[0];
                    $c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
                }
                $c_end = ($c_end > $end) ? $end : $c_end;
                if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
                    header('HTTP/1.1 416 Requested Range Not Satisfiable');
                    header("Content-Range: bytes $start-$end/$size");
                    exit;
                }
                $start = $c_start;
                $end = $c_end;
                $length = $end - $start + 1;
                fseek($fp, $start);
                header('HTTP/1.1 206 Partial Content');
            }
            header("Content-Range: bytes $start-$end/$size");
            header("Content-Length: ".$length);
            $buffer = 1024 * 8;
            while(!feof($fp) && ($p = ftell($fp)) <= $end) {
                if ($p + $buffer > $end) {
                    $buffer = $end - $p + 1;
                }
                set_time_limit(0);
                echo fread($fp, $buffer);
                flush();
            }
            fclose($fp);
            exit();
        } else {
            die('file not found');
        }
    }

    public function renderingJobStatus($logFile)
    {
        $path = $logFile;
        //$path = $this->rootPath . '/uploads/tmp/' . $logFile;
        return $this->videoProcessing->getRenderingPercent($path);
    }

    public function signup(){

        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers");

        $client = Flight::db();
        $request = Flight::request();
        file_put_contents('signup.txt', print_r($request->data, true));
        $db = $client->deltab_app;
        $collection = $db->users;
        $email = $request->data->email;
        $password = $request->data->password;
        $name = $request->data->name;
        $hash_password = password_hash($password,PASSWORD_BCRYPT);
        file_put_contents('email.txt', print_r($email, true));

       $user = [
            "email"=>$email,
            "password" => $hash_password,
           "name" => $name
        ];

        $collection->insertOne($user);
        Flight::json('User Created');
    }
    public function update_name(){
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers");

        $client = Flight::db();
        $request = Flight::request();
        file_put_contents('signup.txt', print_r($request->data, true));
        $db = $client->deltab_app;
        $collection = $db->users;
        $name = $request->data->name["name"];

          $decoded = JWT::decode($request->data->token, 'weareone', array('HS256'));
        $email = (array)$decoded->data->email;


      $updateResult =   $collection->updateOne(
            [ 'email' => $email[0] ],
            [ '$set' => [ 'name' => $name ]]
        );
        file_put_contents('name.txt', print_r($updateResult, true));
        Flight::json('200');
    }
    public function update_user_socketID(){
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        /*header("Access-Control-Max-Age: 3600");*/
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers");
        $client = Flight::db();
        $request = Flight::request();
        $db = $client->deltab_app;
        $collection = $db->users;
        $socketID = $request->data->socketID;

        $decoded = JWT::decode($request->data->token, 'weareone', array('HS256'));
        $email = (array)$decoded->data->email;


        $updateResult =   $collection->updateOne(
            [ 'email' => $email[0] ],
            [ '$set' => [ 'socket_id' => $socketID ]],
            ['$upsert'=>true]
        );
        Flight::json('200');
    }
    public function update_email(){
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers");

        $client = Flight::db();
        $request = Flight::request();
        file_put_contents('signup.txt', print_r($request->data, true));
        $db = $client->deltab_app;
        $collection = $db->users;
        $new_email = $request->data->email["email"];

        $decoded = JWT::decode($request->data->token, 'weareone', array('HS256'));
        $email = (array)$decoded->data->email;


        $updateResult =   $collection->updateOne(
            [ 'email' => $email[0] ],
            [ '$set' => [ 'email' => $new_email ]]
        );
        file_put_contents('name.txt', print_r($updateResult, true));
        Flight::json('200');
    }
    public function update_password(){
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers");

        $client = Flight::db();
        $request = Flight::request();
        file_put_contents('signup.txt', print_r($request->data, true));
        $db = $client->deltab_app;
        $collection = $db->users;
        $password = $request->data->password["password"];
        $hash_password = password_hash($password,PASSWORD_BCRYPT);
        $decoded = JWT::decode($request->data->token, 'weareone', array('HS256'));
        $email = (array)$decoded->data->email;


        $updateResult =   $collection->updateOne(
            [ 'email' => $email[0] ],
            [ '$set' => [ 'password' => $hash_password ]]
        );
        Flight::json('200');
    }
    public function login(){
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers");

        $client = Flight::db();
        $request = Flight::request();
        $db = $client->deltab_app;
        $collection = $db->users;
        $email = $request->data->email;
        $password = $request->data->password;

        if(empty($email) || empty($password)){
           Flight::json([
               'status' => 400,
               'description' => 'Username and Password is Required'
           ]);
           return;
        }

        $result = $collection->findOne(
            [
                'email' => $email
            ]
        );

        if (empty($result)) {
            echo json_encode([
                'status' => 404,
                'error' => 'Template Not Found'
            ]);
            return;
        }

        if(password_verify($password,$result->password)){
            $token = array(
                "iss" =>  "http://example.org",
                "aud" => "http://example.com",
                "iat" => 1356999524,
                "nbf" => 1357000000,
                "data" => array(
                    "id" => $result->_id,
                    "email" => $email
                )
            );

            // set response code
            http_response_code(200);

            // generate jwt
            $jwt = JWT::encode($token,'weareone');
            echo Flight::json(
                array(
                    "message" => "200",
                    "jwt" => $jwt,
                    "name" => $result->name
                )
            );
        } else{
            http_response_code(404);
            echo Flight::json(
                array(
                    "message" => "Invalid Username or password",
                )
            );
        }
    }

    public function getUserVideos(){
        try {
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: application/json; charset=UTF-8");
            header("Access-Control-Allow-Methods: POST");
            header("Access-Control-Max-Age: 3600");
            header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers");
            http_response_code(200);
            $client = Flight::db();
            $db = $client->deltab_app;
            $collection = $db->rendering_jobs;
            $request = Flight::request();
            $data = $request->data;
            $decoded = JWT::decode($data->token, 'weareone', array('HS256'));
            $user_id = (array)$decoded->data->id;
            $user_id = $user_id["\$oid"];
            $result = $collection->find(
                [
                    'user_id' => $user_id
                ]
            );
            $d = iterator_to_array($result);
            if (sizeof($d) > 0) {
                echo Flight::json($d);
            } else {
                echo "Template Not Found";
            }
        }catch(Exception $e){
            echo Flight::json($e->getMessage());
        }
    }

    // This is the real MC, calling send_status.php file.
    public function test_socket($pid){
       /* $socket = new \HemiFrame\Lib\WebSocket\WebSocket("localhost", 3333);
        //$socket->setEnableLogging(true);
        $socketServer = $socket->connect();
        $request = Flight::request();
        $message = $request->query->message;
        $socket->sendData($socketServer,serialize(['frontEndUserID'=> $fID,'percentage'=> $message]));*/

        shell_exec("nohup php send_status.php ".$pid." > /dev/null 2>/dev/null &");
        Flight::json(200);
    }

    // This BC is being called by send_status.php file to make Background Process
    public function emitRenderingStatus($process_id){
        $client = Flight::db();
        $db = $client->deltab_app;
        $collection = $db->rendering_jobs;
        $result = $collection->findOne([
            'job_id' => $process_id
        ]);
        $d = iterator_to_array($result);

        if(!empty($d['user_id'])){

            $uc = $db->users;
            $user_result = $uc->findOne([
               '_id' => new \MongoDB\BSON\ObjectId($d['user_id'])
            ]);
            $d_user = iterator_to_array($user_result);
            if(!empty($d_user['socket_id'])){
                $frontEndUserID =  $d_user['socket_id'];
                /*$request = Flight::request();
                $message = $request->query->message;*/
                $status = $this->renderingJobStatus($d['log_file']);
                $this->socket->sendData($this->socketServer,serialize(['frontEndUserID'=> $frontEndUserID,'percentage'=> $status]));
                if($status>99){
                    $collection->updateOne([
                        'job_id' => $process_id
                    ],[
                        '$set' => ['status' => 'completed']
                    ]);
                }
                Flight::json($status);
                return;
            }else{
                Flight::json([
                    "message" => "Da Fuck?"
                ]);
                return;
            }
        }
        Flight::json([
            "status" => "Must be Used By Renderer"
        ]);
    }
}

$renderer = new DeltaB_Renderer();
Flight::before('json', function () {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE');
    header('Access-Control-Allow-Headers: Content-Type');
});

Flight::before('*', function () {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE');
    header('Access-Control-Allow-Headers: Content-Type');
});

Flight::register('db', 'MongoDB\Client', array('mongodb+srv://deltaB:MyNameIsZee@deltab-cluster-f9vx9.mongodb.net/test?retryWrites=true&w=majority'));
Flight::route('/', array($renderer, 'home'));
Flight::route('/templates/', array($renderer, 'getAllTemplates'));
Flight::route('/media/audio/@audio_name', array($renderer, 'getAudio'));
Flight::route('/media/rendered/video/@video_name', array($renderer, 'getVideo'));
Flight::route('/media/preview/@template', array($renderer, 'getTemplatePreview'));
Flight::route('/rendering_jobs/@logFile', array($renderer, 'renderingJobStatus'));
Flight::route('/soundtracks/', array($renderer, 'getAllSoundtracks'));
Flight::route('/template/@template', function ($template) {
    $renderer = new DeltaB_Renderer();
    Flight::json($renderer->getTemplate($template));
});

Flight::route('OPTIONS /template/*', function () {
    Flight::json('Anyway, return something for OPTIONS requests');
});
Flight::route('OPTIONS /user/*', function () {
    Flight::json('Anyway, return something for OPTIONS requests');
});

Flight::route('/template/@template/render', array($renderer, 'renderTemplate'));
Flight::route('/template/@template/status', array($renderer, 'renderingStatus'));
Flight::route('/user/createTemplate', array($renderer, 'new_template'));

Flight::route('/user/login', array($renderer, 'login'));
Flight::route('/user/signup', array($renderer, 'signup'));
Flight::route('/user/update_name', array($renderer, 'update_name'));
Flight::route('/user/update_password', array($renderer, 'update_password'));
Flight::route('/user/update_email', array($renderer, 'update_email'));
Flight::route('/user/update_socketID', array($renderer, 'update_user_socketID'));
Flight::route('/user/get_videos', array($renderer, 'getUserVideos'));
Flight::route('/user/download/@video_name', array($renderer, 'download_video'));
Flight::route('/testSocket/@pid',array($renderer,'test_socket'));
Flight::route('/emitRenderingStatus/@process_id',array($renderer,'emitRenderingStatus'));
Flight::start();
