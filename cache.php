<?php
echo plugin_dir_path(__FILE__);

$domain = $argv[1];
$ur = explode('api-get-slider',$domain);

$slider_alias = $argv[2];

$ch = curl_init($domain);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
$content = curl_exec($ch);
curl_close($ch);

//check with is_dir()

if(!is_dir("cache/"))
{ mkdir("cache/",0777); } //making directory if doesn't exists


if(!is_dir("cache/assets/"))
{ mkdir("cache/assets/",0777); }
if(!is_dir("cache/assets/css/"))
{ mkdir("cache/assets/css/",0777); }
if(!is_dir("cache/assets/js"))
{ mkdir("cache/assets/js/",0777); }
if(!is_dir("cache/assets/images"))
{ mkdir("cache/assets/images/",0777); }


if(!is_dir("cache/index.html"))
{
  file_put_contents("cache/index.html",$content);
}

$urls = preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $content, $matches); //matching all occurrences of pattern in string

$list = $matches[0];

$domain = $ur[0];
foreach ($list as $url){
$edit_url = explode('?ver',$url);
$url = $edit_url[0];
        $base = basename($url);

        var_dump($url);

        if(preg_match('/\.css/',basename($url))){
                if(strpos($url, 'revslider') !==false){ //check whether substring is present or not
                        //initialize curl session
                        $ch = curl_init($domain.'/wp-content/plugins/wp-digital-signage/cache_files/fonts.zip');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        //setting option for curl transfer
                        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
                        //performing a curl session
                        $content_fonts = curl_exec($ch);
                        curl_close($ch);

                        $ch = curl_init($domain.'/wp-content/plugins/wp-digital-signage/cache_files/assets.zip');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        //setting option for curl transfer
                        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
                        $content_assets = curl_exec($ch);
                        curl_close($ch);

                        //writing the contents to the files
                        file_put_contents("cache/assets/fonts.zip",$content_fonts);
                        file_put_contents("cache/assets/assets.zip",$content_assets);

                        $zip = new ZipArchive;
                        //open a zip file archive
                        $res = $zip->open('cache/assets/assets.zip');
                        if ($res === TRUE) {
                          //extracting file
                          $zip->extractTo('cache/assets/');
                          $zip->close();
                        }


                        $zip = new ZipArchive;
                        //open a zip file archive
                        $res = $zip->open('cache/assets/fonts.zip');
                        if ($res === TRUE) {
                          //extracting file
                          $zip->extractTo('cache/assets/');
                          $zip->close();
                        }

                }

                        file_put_contents("cache/assets/css/$base",file_get_contents($url));
$content = str_replace($url,$domain.'cache/assets/css/'.$base,$content);
        }
        else if(preg_match('/\.js/',basename($url))){
                file_put_contents("cache/assets/js/$base",file_get_contents($url));
$content = str_replace($url,$domain.'cache/assets/js/'.$base,$content);
        }
        else if(preg_match('/\.(bmp|jpeg|gif|png|jpg)/',basename($url))){
                file_put_contents("cache/assets/images/$base",file_get_contents($url));
$content = str_replace($url,$domain.'cache/assets/images/'.$base,$content);
        }
        echo '<br />';
}

file_put_contents("cache/index.html",$content);

//Create zip file

    $zipname = "cache-$slider_alias.zip";
    $zip = new ZipArchive;
    $zip->open($zipname, ZipArchive::CREATE);

$rootPath = realpath('cache/');


// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file)
{
    // Skip directories (they would be added automatically)
    if (!$file->isDir())
    {
        // Get real and relative path for current file
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($rootPath) + 1);

        // Add current file to archive
        $zip->addFile($filePath, $relativePath);
    }
}

?>
