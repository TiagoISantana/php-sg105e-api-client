<?php

namespace SG105E;

use CurlHandle;

/**
 *
 */
class Client
{

    /**
     * @var false|CurlHandle
     */
    private $_ch;

    private string $_base_uri;

    /**
     * @param string $username
     * @param string $password
     */
    public function __construct(public string $ip, public string $username, public string $password)
    {

        $this->_base_uri = "http://{$ip}/";

        $this->_ch = curl_init();
        curl_setopt_array(
            $this->_ch,
            [
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_SSL_VERIFYHOST => FALSE,
                CURLOPT_RETURNTRANSFER => TRUE,
            ]
        );

        $this::login();

    }

    /**
     * @return bool|string
     */
    private function login(): bool|string
    {

        curl_setopt_array(
            $this->_ch,
            [
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_URL => "{$this->_base_uri}logon.cgi",
                CURLOPT_POSTFIELDS => http_build_query(
                    [
                        'username' => $this->username,
                        'password' => $this->password,
                        'cpassword',
                        'logon' => 'Login',
                    ]
                )
            ]
        );

        return curl_exec($this->_ch);

    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getInfo(): array
    {

        curl_setopt_array(
            $this->_ch,
            [
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_URL => "{$this->_base_uri}SystemInfoRpm.htm",
            ]
        );

        $info = curl_exec($this->_ch);

        if(!str_contains($info,'info_ds'))
            throw new \Exception('Unable to retrieve information');

        $info = $this->get_between($info,'var info_ds = ',';');

        return [
            'device_description'=>trim($this->get_between($info,'descriStr:[','],')),
            'mac_address'=>trim($this->get_between($info,'macStr:[','],')),
            'ip'=>trim($this->get_between($info,'ipStr:[','],')),
            'subnet_mask'=>trim($this->get_between($info,'netmaskStr:[','],')),
            'default_gateway'=>trim($this->get_between($info,'gatewayStr:[','],')),
            'firmware_version'=>trim($this->get_between($info,'firmwareStr:[','],')),
            'hardware_version'=>trim($this->get_between($info,'hardwareStr:[','],')),
        ];


    }

    /**
     * @param string $name
     * @return bool|string
     * @throws \Exception
     */
    public function set_system_name(string $name): bool|string
    {

        if(strlen($name) > 32)
            throw new \Exception('The length of device description should not be more than 32 characters.');

        preg_match("/^[a-zA-Z0-9]+$/", $name);

        curl_setopt_array(
            $this->_ch,
            [
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_URL => "{$this->_base_uri}system_name_set.cgi?sysName=$name",
            ]
        );

        return curl_exec($this->_ch);

    }

    /**
     * @param bool $dhcp
     * @param string $ip_address
     * @param string $ip_netmask
     * @param string $ip_gateway
     * @return bool|string
     * @throws \Exception
     */
    public function ip_setting(bool $dhcp, string $ip_address, string $ip_netmask, string $ip_gateway): bool|string
    {

        if(!filter_var($ip_address, FILTER_VALIDATE_IP) !== false)
            throw new \Exception('Not a valid ip address');

        if(!filter_var($ip_netmask, FILTER_VALIDATE_IP) !== false)
            throw new \Exception('Not a network mask');

        if(!filter_var($ip_gateway, FILTER_VALIDATE_IP) !== false)
            throw new \Exception('Not a valid gateway');

        if($dhcp)
            $dhcp = 'enable';
        else
            $dhcp = 'disable';

        curl_setopt_array(
            $this->_ch,
            [
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_URL => "{$this->_base_uri}ip_setting.cgi?dhcpSetting=$dhcp&ip_address=$ip_address&ip_netmask=$ip_netmask&ip_gateway=$ip_gateway",
            ]
        );

        return curl_exec($this->_ch);

    }

    /**
     * @param int $status
     * @return bool|string
     * @throws \Exception
     */
    public function led_control(int $status): bool|string
    {

        if($status > 1)
            throw new \Exception('Invalid led control option');

        curl_setopt_array(
            $this->_ch,
            [
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_URL => "{$this->_base_uri}led_on_set.cgi?rd_led=$status&led_cfg=Apply",
            ]
        );

        return curl_exec($this->_ch);

    }

    /**
     * @return bool|string
     */
    public function reboot(): bool|string
    {

        curl_setopt_array(
            $this->_ch,
            [
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_URL => "{$this->_base_uri}reboot.cgi",
                CURLOPT_POSTFIELDS => http_build_query(
                    [
                        'reboot_op' => 'reboot',
                        'save_op' => 'false',
                    ]
                )
            ]
        );

        return curl_exec($this->_ch);

    }

    /**
     * @param $content
     * @param $start
     * @param $end
     * @return string
     */
    private function get_between($content, $start, $end)
    {
        $r = explode($start, $content);
        if (isset($r[1])) {
            $r = explode($end, $r[1]);
            return $r[0];
        }
        return '';
    }

    /**
     *
     */
    public function __destruct()
    {

        curl_setopt_array(
            $this->_ch,
            [
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_URL => "{$this->_base_uri}Logout.htm",
            ]
        );

        curl_close($this->_ch);

    }

}

