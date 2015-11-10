<?php

namespace Jp\Twilio;

class Twilio {

    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    public function GetCall($callsid)
    {
        $twilio = $this->getTwilio();
        // Create Call via Twilio SDK
        return $twilio->account->calls->get($callsid);
    }

    public function UpdateCall($call,$args)
    {
        $call->update($args);
        return $call->to;
    }

    public function Capability($userId) {
        $capability = new \Services_Twilio_Capability(
            $this->config['sid'], 
            $this->config['token']
        );
        $userId = str_replace("-", "", $userId);
        $capability->allowClientIncoming($userId);
        $capability->allowClientOutgoing($this->config['app_sid']);

        return $capability->generateToken(3600*24);
    }

    public function AvailablePhoneNumbers($type="Local",$SearchParams=array()) {
        $twilio = $this->getTwilio();
        $numbers = $twilio->account->available_phone_numbers->getList('US', $type, $SearchParams);
        
        return $numbers->available_phone_numbers;
    }

    public function Purchase($phone) {
        $twilio = $this->getTwilio();
        $numbers = $twilio->account->incoming_phone_numbers->create(array(
            "PhoneNumber" => $phone
        ));
        
        return $numbers->sid;
    }

    public function message($to, $message, $from=null) {
        $twilio = $this->getTwilio();
        // Send SMS via Twilio SDK
        return $twilio->account->messages->sendMessage(
            is_null($from) ? $this->config['from'] : $from,
            $to,
            $message
        );
    }

    public function call($to, $url, $options=array(), $from=null) {
        $twilio = $this->getTwilio();
        // Create Call via Twilio SDK
        return $twilio->account->calls->create(
            is_null($from) ? $this->config['from'] : $from,
            $to,
            $url,
            $options);
    }

    public function twiml($callback)
    {
        $message = new \Services_Twilio_Twiml();

        if( $callback instanceof \Closure ) {
            call_user_func($callback, $message);
        } else {
            throw new \InvalidArgumentException("Callback is not valid.");
        }

        return $message->__toString();

    }

    public function getTwilio()
    {
        if (array_key_exists('ssl_verify', $this->config) 
            && false === $this->config['ssl_verify']) {

            $http = new \Services_Twilio_TinyHttp(
                'https://api.twilio.com',
                array('curlopts' => 
                    array(
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => 2,
                    )
                )
            );

            return new \Services_Twilio(
                $this->config['sid'], 
                $this->config['token'], 
                null, 
                $http
            );
        }

        return new \Services_Twilio($this->config['sid'], $this->config['token']);
    }

}
