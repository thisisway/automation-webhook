<?php
namespace App\Services;

use Kernel\Env;

class Weather {
    public static function getWeatherData($city, $state, $country)
    {
        $key = Env::get('OPEN_WEATHER_KEY');
        $endpoint = "https://api.openweathermap.org/data/2.5/weather?q={$city},{$state},{$country}&appid={$key}&units=metric";
        return self::getCurl($endpoint);
    }

    public static function getWeatherDataByIp($ip) {

        if(Env::get('APP_ENV') == 'local')
            $ip = '201.148.120.8';

        $endpoint = "http://ip-api.com/json/{$ip}";
        $dataIp = self::getCurl($endpoint);
        
        return self::getWeatherCoodenates($dataIp->lat, $dataIp->lon);
    }

    public static function getWeatherCoodenates($latitude, $longitude) {
        $key = Env::get('OPEN_WEATHER_KEY');
        $endpoint = "https://api.openweathermap.org/data/2.5/weather?lat={$latitude}&lon={$longitude}&appid={$key}&units=metric&lang=pt_br";
        return self::getCurl($endpoint);
    }

    private static function getCurl($endpoint)
    {
        $curl = curl_init();
        
        $options = [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ];

        curl_setopt_array($curl, $options);
        $response   = curl_exec($curl);
        $err        = curl_error($curl);
        $info       = curl_getinfo($curl);
        curl_close($curl);

        if($err) 
            return false;

        return json_decode($response);
    }
}