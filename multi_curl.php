<?
class CURL
{
    private $curlm, $handles, $errors, $active;
    public $response, $info;
    public function __construct()
    {
        // cURL multi is faster for multiple requests, useful for querying API's
        $this->curlm = curl_multi_init();
    }
    // make a new cURL handle directly from the class
    public function new_handle($url, $options=[])
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.3; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
        if(!empty($options))
            curl_setopt_array($ch, $options);
        $this->handles[$url] = $ch;
    }
    // lets you insert existing cURL handles
    public function add_handle($handle)
    {
        return curl_multi_add_handle($this->curlm, $handle);
    }
    // $id can either be the URL for a handle or the index in the array of handles
    public function remove_handle($id)
    {
        $handle = $this->handles[$id];
        curl_multi_remove_handle($this->curlm, $handle);
        unset($this->handles[$id]);
        return $handle;
    }
    public function exec($close=true)
    {
        foreach($this->handles as $handle)
            curl_multi_add_handle($this->curlm, $handle);
        do {
            $this->errors[] = curl_multi_exec($this->curlm, $this->active); // add your own error handling, this array really does nothing right now
            curl_multi_select($this->curlm);
        } while ($this->active > 0);
        foreach ($this->handles as $url=>$handle)
            $this->response[$url] = curl_multi_getcontent($handle);
        $this->info = curl_multi_info_read($this->curlm); // comment out this line if you want to save some cpu cycles
        if($close)
            curl_multi_close($this->curlm);
        return $this->response;
    }
}