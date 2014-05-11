<?php
/**
 * CPanel for Clients to unblock the blocked IP.
 *
 * @package nirays
 * @subpackage nirays.plugins.cpanel_csf
 * @copyright Copyright (c) 2013, Nirays Technologies.
 * @license License Agreement
 * @link http://nirays.com/ Nirays
 */
class Csf {
    /**
     * To check if it is a valid IP.
     * @param $ip
     * @return bool
     */
    public function validateIP($ip){
        return inet_pton($ip) !== false;
    }
    /**
     * Function to unblock ip
     * @param $hostname
     * @param $ip
     * @param $newheader
     * @return bool
     */
    function unBlock($hostname,$ip,$newheader,$use_ssl,$username){
         $port = ($use_ssl ? 2087 : 2086);
        $result = array();
        $result["status"] = false;
        $result["msg"] = "";
         $protocol = ("http" . ($use_ssl ? "s" : ""));
         $url = "$protocol://$hostname:$port/cgi/configserver/csf.cgi";
        if(strtolower ($username)=="root"){
            $action="kill";
        }
        else{
            $action="qkill";
        }

        if($this->validateIP($ip) == false)
         {
             $result["msg"] = "Invalid IP";
             return $result;
         }
         $args["action"] = $action;
         $args["ip"] = $ip;

         if($args) {
             $query_string = '?';
             foreach ($args AS $k=>$v) $query_string .= "$k=".urlencode($v)."&";
         }else{
             $query_string ='';
         }

         $query_url = $url.$query_string;

         $curl = curl_init();
         # Create Curl Object
         curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
         # Allow self-signed certs
         curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
         # Allow certs that do not match the hostname
         curl_setopt($curl, CURLOPT_HEADER,0);
         # Do not include header in output
         curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
         # Return contents of transfer on curl_exec
         $header[0] = $newheader;
         curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
         # set the username and password
         curl_setopt($curl, CURLOPT_URL, $query_url);
         # execute the query
         $res = curl_exec($curl);
         if ($res == false) {
             error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query_url");
             $result["msg"] = curl_error($curl);
             # log error if curl exec fails
         }
        // Check if any error occurred
        if(curl_errno($curl))        {
            $result["msgCode"] = curl_errno($curl);
        }
        else {
            $info = curl_getinfo($curl);
            $result["status"] = true;
            $result["msg"] = $res;
            $result["msgCode"] = $info['http_code'];
            if($result["msgCode"]>=200 &&  $result["msgCode"]<300) {
                preg_match("/<table[^>]*>(.*?)<\\/table>/si", $res, $match);
                $result["msg"] = $match[1];
            }
        }
         curl_close($curl);
        return $result;
    }
    /**
     * Returns response
     *
     * @return array An array of details.
     */
    public function unBlockIpUsingKey($serverhostname,$ip,$username,$pkey,$use_ssl) {
        $pkey = str_replace("\r\n",'',$pkey); # Strip newlines from the hash
        $newHeader = "Authorization: WHM $username:" . preg_replace("'(\r|\n)'","",$pkey);
        return $this->unBlock($serverhostname,$ip,$newHeader,$use_ssl,$username);
    }

    public function unBlockIp($serverhostname,$ip,$username,$password,$use_ssl){
        $newHeader = "Authorization: Basic " . base64_encode($username.":".$password) . "\n\r";
        return $this->unBlock($serverhostname,$ip,$newHeader,$use_ssl,$username);
    }


}

?>