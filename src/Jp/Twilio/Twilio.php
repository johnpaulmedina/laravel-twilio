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

    // Lead Trust
    // Company ID:   2fd57a80-f93a-11e4-905b-23bd4943c732
    // Account SID:  ACb5698fa310df3dd9c06aee561e56fca2
    // Auth Token:   95839b52989b9edc2dfbf80ddde6ac7f

    // LT TV & Internet
    // Company ID:   2184abb0-00d5-11e6-bd72-0d276f541785
    // Account SID:  AC11c40f4629bd592a9e51b7dcad11d6d8
    // Auth Token:   a29eb338f88ef1c90ce011d0c223f095

    public function Capability($userId,$companyId = NULL) {
        
        if($companyId && array_key_exists($companyId,$this->config)) {
            $capability = new \Services_Twilio_Capability(
                $this->config[$companyId]['sid'], 
                $this->config[$companyId]['token']
            );
        } else {
            $capability = new \Services_Twilio_Capability(
                $this->config['sid'], 
                $this->config['token']
            );
        }

        $userId = str_replace("-", "", $userId);
        $capability->allowClientIncoming($userId);

        if($companyId && array_key_exists($companyId,$this->config)) {
            $capability->allowClientOutgoing($this->config[$companyId]['app_sid']);
        } else {
            $capability->allowClientOutgoing($this->config['app_sid']);
        }

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
