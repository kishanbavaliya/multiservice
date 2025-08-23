<?php

namespace App\Support\MediaLibrary\Downloaders;

use Spatie\MediaLibrary\Downloaders\Downloader;

class CustomDownloader implements Downloader
{
    public function getTempFile(string $url): string
    {
        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36",
            ],
        ]);

        try {
            $stream = file_get_contents($url, false, $context);
        } catch (\Exception $ex) {
            $stream = $this->curl_get_file_contents($url);
        }

        $temporaryFile = tempnam(sys_get_temp_dir(), 'media-library');

        file_put_contents($temporaryFile, $stream);

        return $temporaryFile;
    }




    //
    public function curl_get_file_contents($URL)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);

        if ($contents) return $contents;
        else return FALSE;
    }
}
