<?php

namespace App\Http\Controllers;

use App\Models\LiveboxPlay;
use Laravel\Lumen\Routing\Controller as BaseController;

class LiveboxPlayController extends BaseController {

    protected $address;
    protected $livebox;

    public function __construct() {
        $this->address = config('app.livebox_address');
        $this->livebox = new LiveboxPlay($this->address);
    }

    public function turnOn() {
        // TODO: implement http://192.168.0.14:8080/remoteControl/cmd?operation=10 to check the status
        $command = $this->livebox->changeState(true);
        if($command['code'] == 200) {
            return response(['status' => $command['status']], $command['code']);
        } else {
            return response(['error' => $command['error']], $command['code']);
        }
    }

    public function turnOff() {
        $command = $this->livebox->changeState(false);
        if($command['code'] == 200) {
            return response(['status' => $command['status']], $command['code']);
        } else {
            return response(['error' => $command['error']], $command['code']);
        }
    }

    public function getState() {
        $command = $this->livebox->getState();
        if($command['code'] == 200) {
            return response(['state' => $command['state']], $command['code']);
        } else {
            return response(['error' => $command['error']], $command['code']);
        }

    }

    public function changeChannel() {
        // Example query: curl "http://192.168.0.14:8080/remoteControl/cmd?operation=09&epg_id=*******192&uui=1"
        // Service list: http://lsm-rendezvous040413.orange.fr/API/
    }



}
