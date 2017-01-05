<?php

namespace App\Models;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

class LiveboxPlay {
    protected $address;
    protected $client;

    public function __construct($address) {
        $this->client = new Client();
        $this->address = $address;
    }

    protected function sendRequest($uri) {
        try {
            $res = $this->client->request('GET', $this->address.$uri, ['connect_timeout' => 1]);
        } catch (ConnectException $e) {
            // Livebox can't be contacted or any other generic error
            return [
                'error' => 'Livebox Play unreachable',
                'code' => 503
            ];
        }

        $body = json_decode($res->getBody(), true);

        // The HTTP request got something wrong (endpoint or command)
        if($res->getStatusCode() != 200) {
            return [
                'error' => 'Livebox Play generic error',
                'code' => 500
            ];
        }

        // Got a 200 code
        if($body['result']['responseCode'] == "0") {
            // Command OK
            return ['data' => $body['result']['data'], 'code' => 200];
        } else {
            // Get the error message sent by the Livebox
            return ['error' => $body['result']['message'], 'code' => 401];
        }
    }

    public function getState() {
        $res = $this->sendRequest("/remoteControl/cmd?operation=10");
        if($res['code'] == 200) {
            // We must return the inverse as the value is for the standby state
            return ['state' => !$res['data']['activeStandbyState'], 'code' => 200];
        } else {
            return $res;
        }
    }

    public function changeState($state) {
        $checkState = $this->sendRequest("/remoteControl/cmd?operation=10");
        if($checkState['code'] == 200) {
            if($checkState['data']['activeStandbyState'] == $state) {
                $res = $this->sendRequest("/remoteControl/cmd?operation=01&key=116&mode=0");
                if($res['code'] == 200) {
                    return ['status' => "ok", 'code' => 200];
                } else {
                    return $res;
                }
            } else {
                return ['status' => "ok", 'code' => 200];
            }
        }
        return $checkState;
    }

    public function changeChannel($channel) {
        // Example query: curl "http://192.168.0.14:8080/remoteControl/cmd?operation=09&epg_id=*******192&uui=1"
        // Service list: http://lsm-rendezvous040413.orange.fr/API/

        $channels = [
            '1' => 192,
            '2' => 4,
            '3' => 80,
            '4' => 34,
            '5' => 47,
            '6' => 118,
            '7' => 111,
            '8' => 445,
            '9' => 119,
            '10' => 195,
            '11' => 446,
            '12' => 444,
            '13' => 234,
            '14' => 78,
            '15' => 481,
            'bfm' => 481,
            '16' => 226,
            '17' => 458,
            '18' => 482,
            '19' => 160,
            '20' => 1404,
            '21' => 1401,
            '22' => 1403,
            '23' => 1402,
            '24' => 1400,
            '25' => 1399,
            '26' => 112,
        ];

        if(!array_key_exists($channel, $channels)) {
            return ['error' => "not found", 'code' => 404];
        }

        $res = $this->sendRequest("/remoteControl/cmd?operation=09&epg_id=*******".$channels[$channel]."&uui=1");
        if($res['code'] == 200) {
            return ['status' => "ok", 'code' => 200];
        } else {
            return $res;
        }
    }

    public function changeVolume($direction, $value) {
        $key = ($direction == "up") ? '115' : '114';
        $i = 0;
        while($i < $value) {
            $res = $this->sendRequest("/remoteControl/cmd?operation=01&key=".$key."&mode=0");
            $i++;
        }
        return ['status' => "ok", 'code' => 200];
    }

}