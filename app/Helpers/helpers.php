<?php
# https://laravel-news.com/creating-helpers
// "files": ["app/Helpers/helpers.php"]
# composer dump-autoload
// if (! function_exists('bark_notify')) {}
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

/**
 * Format the parameters for the logger.
 *
 * @param  mixed  $message
 * @return mixed
 * copyfrom Illuminate\Log\Logger::formatMessage;
 */
function toString($message)
{
    if (is_array($message)) {
        return var_export($message, true);
    } elseif ($message instanceof Jsonable) {
        return $message->toJson();
    } elseif ($message instanceof Arrayable) {
        return var_export($message->toArray(), true);
    }

    return $message;
}


function xStringToJson($xmlString)
{ 
    return json_encode(simplexml_load_string($xmlString));
}

function xStringToArray($xmlString)
{ 
    return json_decode(xStringToJson($xmlString), TRUE);
}


// https://day.app/2018/06/bark-server-document/
// https://www.v2ex.com/t/467407
// // eg:
// bark_notify('验证码是0000');
// bark_notify('验证码是0001','这是body解释');
// bark_notify('点击打开网址', 'https://cn.bing.com');
// bark_notify('验证码是1234，已复制1234到剪切板，粘贴即可。','12345', true);
// bark_notify('验证码是4567，已复制所有文本到剪切板。', false, true);
function bark_notify($title, $bodyOrUrlOrCopy = false, $copy = false, $sendTo = 'admin', $host = 'https://api.day.app')
{
    $key = Config::get('services.bark.'.$sendTo);
    $url = $host.'/'.$key.'/'.urlencode($title);
    $query = [];
    if (filter_var($bodyOrUrlOrCopy, FILTER_VALIDATE_URL)) {
        $query['url'] = $bodyOrUrlOrCopy;
    } else {
        //有copy就不要body了！
        if ($copy) {
            $query['copy'] = $bodyOrUrlOrCopy ?: $title; //如果没有填写body，复制title
        } else {
            $url .= '/'.urlencode($bodyOrUrlOrCopy);
        }
    }
    $query['automaticallyCopy'] = 1;
    // $query['sound'] = 'telegraph';
    $postdata = http_build_query($query);
    $opts = [
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postdata,
        ],
    ];
    $context = stream_context_create($opts);

    return @file_get_contents($url, false, $context);
}


// $classes = getClassesList(app_path('Models'));
function getClassesList($dir)
{
    $classes = File::allFiles($dir);
    foreach ($classes as $class) {
        $class->classname = str_replace(
            [app_path(), '/', '.php'],
            ['App', '\\', ''],
            $class->getRealPath()
        );
    }

    return $classes;
}
