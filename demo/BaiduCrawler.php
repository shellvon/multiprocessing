<?php
/**
 * Created by shellvon.
 *
 * @author: shellvon<iamshellvon@gmail.com>
 * @date: 2016/10/28
 * @time: 下午6:18
 *
 * @version 1.0
 */
class BaiduCrawler extends \MultiProcessing\Worker
{
    protected $baseUrl = 'http://www.123.com/';

    protected $reqMethod = 'GET';

    protected $curlOptions = array(
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0,
        CURLOPT_FOLLOWLOCATION => true, // To make cURL follow a redirect
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => 'utf8',
        CURLOPT_USERAGENT => 'Crawl Everything Demo',
    );

    protected $timeout = 7;

    protected $params = null;

    public function getParams()
    {
        return $this->params;
    }

    public function setBaseUrl($url){
        $this->baseUrl = $url;
    }

    public function getBaseUrl(){
        return $this->baseUrl;
    }
    protected function process($arguments = null)
    {
        $this->params = $arguments;
        parent::process();

        return $this->request($this->params['params'], $this->params['endPoint']);
    }

    protected function request($params = null, $endPoint = null)
    {
        $params = (array) $params;
        $this->params = $params;
        $url = $this->baseUrl.$endPoint;
        $ch = curl_init();
        foreach ($this->curlOptions as $k => $v) {
            curl_setopt($ch, $k, $v);
        }
        switch ($this->reqMethod) {
            case 'GET':
                $contact_char = strpos($url, '?') === false ?  '?' : '&';
                $url = $url.$contact_char.http_build_query($params, null, '&');
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                break;
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        $response = curl_exec($ch);
        if ($response === false) {
            throw new \Exception(curl_error($ch), 500);
        }
        curl_close($ch);

        return $response;
    }
}
