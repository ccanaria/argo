<?php

namespace Argo;

/**
 * The primary Argo class.
 * Handles package interactions.
 * 
 * @see https://github.com/dsposito/argo
 */
class Package
{
    /**
     * Original, provided tracking code.
     *
     * @var string
     */
    public $tracking_code_original;

    /**
     * True tracking code (sans provider prefix/suffix characters).
     *
     * @var string
     */
    public $tracking_code;

    /**
     * Carrier data.
     *
     * @var Argo\Carrier
     */
    public $carrier;

    /**
     * Provider data.
     *
     * @var Argo\Provider
     */
    public $provider;

    /**
     * Creates a package instance based on a tracking code.
     *
     * @param string $tracking_code The package tracking code.
     *
     * @return Argo\Package
     */
    public static function instance($tracking_code)
    {
        $tracking_code = preg_replace('/[^A-Z0-9]/i', '', $tracking_code);

        $instance = new self();
        $instance->tracking_code_original = $tracking_code;
        $instance->tracking_code          = $tracking_code;

        return $instance->deduceTrackingCode();
    }

    /**
     * Gets this package's carrier code.
     *
     * @return string
     */
    public function getCarrierCode()
    {
        if (!$this->carrier instanceof Carrier) {
            return false;
        }

        return $this->carrier->code;
    }
    /**
     * Gets this package's carrier name.
     *
     * @return string
     */
    public function getCarrierName()
    {
        if (!$this->carrier instanceof Carrier) {
            return false;
        }

        return $this->carrier->name;
    }

    /**
     * Gets this package's provider code.
     *
     * @return string
     */
    public function getProviderCode()
    {
        if (!$this->provider instanceof Provider) {
            return false;
        }

        return $this->provider->code;
    }

    /**
     * Gets this package's provider name.
     *
     * @return string
     */
    public function getProviderName()
    {
        if (!$this->provider instanceof Provider) {
            return false;
        }

        return $this->provider->name;
    }

    /**
     * Gets the tracking code.
     *
     * @param bool $return_original Whether or not to return the original or true tracking code.
     *
     * @return string
     */
    public function getTrackingCode($return_original = false)
    {
        return $return_original ? $this->tracking_code_original : $this->tracking_code;
    }

    /**
     * Determines the package's shipping details based on its tracking code.
     *
     * @return void
     */
    private function deduceTrackingCode()
    {
        $tracking_code = $this->tracking_code;
        $carrier_code  = null;
        $provider_code = null;

        if (preg_match('/^[0-9]{2}[0-9]{4}[0-9]{4}$/', $tracking_code, $matches)) {
            $carrier_code = 'dhl';
        }
        else if (preg_match('/^([0-9]{20})?([0-9]{4}[0-9]{4}[0-9]{4}[0-9]{2})$/', $tracking_code, $matches)) {
            $this->tracking_code = $matches[2];

            $carrier_code = 'fedex';
        }
        else if (preg_match('/(\b96\d{20}\b)|(\b\d{15}\b)|(\b\d{12}\b)/', $tracking_code, $matches)) {
        	$this->tracking_code = $matches[2];
        
        	$carrier_code = 'fedex';
        }
        else if (preg_match('/\b((98\d\d\d\d\d?\d\d\d\d|98\d\d) ?\d\d\d\d ?\d\d\d\d( ?\d\d\d)?)\b/', $tracking_code, $matches)) {
        	$this->tracking_code = $matches[2];
        
        	$carrier_code = 'fedex';
        }
        else if (preg_match('/^[0-9]{15}$/', $tracking_code, $matches)) {
        	$this->tracking_code = $matches[2];
        
        	$carrier_code = 'fedex';
        }
        else if (preg_match('/^1Z[A-Z0-9]{3}[A-Z0-9]{3}[0-9]{2}[0-9]{4}[0-9]{4}$/i', $tracking_code)) {
            $carrier_code = 'ups';
        }
        else if (preg_match('/\b(1Z ?[0-9A-Z]{3} ?[0-9A-Z]{3} ?[0-9A-Z]{2} ?[0-9A-Z]{4} ?[0-9A-Z]{3} ?[0-9A-Z]|[\dT]\d\d\d ?\d\d\d\d ?\d\d\d|\d{22})\b/i', $tracking_code)) {
        	$carrier_code = 'ups';
        }
        else if (preg_match('/\b(1Z ?[0-9A-Z]{3} ?[0-9A-Z]{3} ?[0-9A-Z]{2} ?[0-9A-Z]{4} ?[0-9A-Z]{3} ?[0-9A-Z]|[\dT]\d\d\d ?\d\d\d\d ?\d\d\d)\b/', $tracking_code)) {
        	$carrier_code = 'ups';
        }
        else if (preg_match('/^[0-9]{4}[0-9]{4}[0-9]{4}[0-9]{4}[0-9]{4}[0-9]{2}$/', $tracking_code)) {
            $carrier_code = 'usps';
        }
        elseif (preg_match('/^420[0-9]{5}([0-9]{4}[0-9]{4}[0-9]{4}[0-9]{4}[0-9]{4}[0-9]{2})$/', $tracking_code, $matches)) {
        	$this->tracking_code = $matches[1];
        
        	$carrier_code  = 'usps';
        	$provider_code = 'endicia';
        }
        else if (preg_match('/(\b\d{30}\b)|(\b91\d+\b)|(\b\d{20}\b)/', $tracking_code)) {
        	$carrier_code = 'usps';
        }
        else if (preg_match('/^E\D{1}\d{9}\D{2}$|^9\d{15,21}$/', $tracking_code)) {
        	$carrier_code = 'usps';
        }
        else if (preg_match('/^91[0-9]+$/', $tracking_code)) {
        	$carrier_code = 'usps';
        }
        else if (preg_match('/^[A-Za-z]{2}[0-9]+US$/', $tracking_code)) {
        	$carrier_code = 'usps';
        }
        
        if (!empty($carrier_code)) {
            $this->carrier  = new Carrier($carrier_code);
            $this->provider = new Provider($provider_code ?: $carrier_code);
        }

        return $this;
    }
}
