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

    public function changeChannel() {
        // Example query: curl "http://192.168.0.14:8080/remoteControl/cmd?operation=09&epg_id=*******192&uui=1"
        // Service list: http://lsm-rendezvous040413.orange.fr/API/
    }

}