<?php
// Clears the cache and prevent unwanted output
ob_clean();
@ini_set('error_reporting', E_ALL & ~ E_NOTICE);
@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 'Off');
session_start();

$video_location = 'videos/';
if (!isset($_SESSION['logged'])) {
    # code...
    echo "403 forbidden";
    die();
}
if (!isset($_GET['vid'])) {
    # code...
    echo "404 not found";
    die();
}
$file = $video_location.$_GET['vid'] ; // The media file's location
$mime = "application/octet-stream";
$size = filesize($file); // The size of the file

if(isset($_SERVER['HTTP_RANGE'])){
    $chunkSize = 8192; // The size of each chunk to output
    $parts_per_connection = 100; // no of parts per connection
    $connection_size = $chunkSize * $parts_per_connection; // max size the connection will provide
    header('Connection: Keep-Alive, close');
    header('Content-type: ' . $mime);
    $ranges = array_map(
        'intval', // Parse the parts into integer
        explode(
            '-', // The range separator
            substr($_SERVER['HTTP_RANGE'], 6) // Skip the `bytes=` part of the header
        )
    );

    // If the last range param is empty, it means the EOF (End of File)
    if(!$ranges[1]){
        $ranges[1] = $size - 1;
    }
  
    $remaining = ($ranges[1] - $ranges[0]);

    header('HTTP/1.1 206 Partial Content');
    header('Accept-Ranges: bytes');
    header('Content-Length: ' . ($remaining < $connection_size ? $remaining : $connection_size));

    header(
        sprintf(
            'Content-Range: bytes %d-%d/%d', // The header format
            $ranges[0],
            $ranges[1], 
            $size 
        )
    );

    $f = fopen($file, 'rb'); // Open the file in binary mode
   

    // Seek to the requested start range
    fseek($f, $ranges[0]);

    // Start outputting the data
    while($parts_per_connection--){
        // Check if EOF already
        if(ftell($f) >= $ranges[1]){
            break;
        }

        // Output the data
        echo fread($f, $chunkSize);

        @ob_flush();
        flush();
    }
}
