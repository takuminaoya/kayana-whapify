<?php

namespace Kayana\Whapify;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

/**
 * Whapify API
 *
 * Class yang digunakan untuk mempermudah implementasi whapify api
 *
 * @copyright  2025 Kayana Creative, I Wayan Oka Cipta W
 * @license    MIT
 * @version    Release: 1.0
 * @link       -
 * @since      Class available since Release 1.0.0 @ April 2025
 */
class Whapify
{
    private $version = "1.0.6.5-stable";

    private $account;
    private $secret;

    private $disk, $path, $filename, $public_disk, $public_path, $public_name, $base_url;

    /** 
     * Whapify API Class From Kayana
     * 
     * @param accountID $acc dapatkan id pada https://whapify.id/dashboard/hosts/whatsapp
     * @param secretID $sec dapatkan secret pada https://whapify.id/dashboard/tools/keys
     * **/
    function __construct()
    {

        // Init data berdasarkan config/whapify.php
        $this->disk = config('whapify.disk');
        $this->path = config('whapify.path');
        $this->filename = config('whapify.filename');

        $this->public_disk = config('whapify.public_disk');
        $this->public_path = config('whapify.public_path');
        $this->public_name = config('whapify.public_name');

        $this->base_url = config('whapify.base_url');

        // init berdasarkan file whatsapp.json jika lokal, whatsapp.key jika produksi
        $cred = $this->getWhatsappSetting();

        $this->account = $cred ? $cred['account'] : null;
        $this->secret = $cred ? $cred['secret'] : null;
    }

    /** 
     * Dapatkan variable account
     * @return accountID
     * **/
    function getAccount()
    {
        return $this->account;
    }

    /** 
     * Dapatkan variable secret
     * @return secretID
     * **/
    function getSecret()
    {
        return $this->secret;
    }

    /**
     * Fungsi untuk update value config whapify
     * @param accountID $account
     * @param secretID $secret
     * @return array accountID dan Secret
     */
    function setCredential($account, $secret)
    {
        if (env('APP_ENV') == "production")
            return [
                "code" => 500,
                "message" => "Anda telah memasuki mode produksi. pembaharuan hanya bisa dilakukan saat msh dalam keadaan lokal."
            ];

        $exist = Storage::disk($this->disk)->exists($this->path . "/" . $this->filename);

        if (!$exist)
            return [
                "code" => 404,
                "message" => "File tidak ada pada sistem. pastikan module whapy telah aktif. gunakan command ini untuk membuat file json config whatsapp : php artisan kayana:whatsapp"
            ];

        $setting = $this->getWhatsappSetting();

        $setting["account"] = $account;
        $setting["secret"] = $secret;

        $jSetting = json_encode($setting);

        Storage::disk($this->disk)->put($this->path . "/" . $this->filename, $jSetting);

        return [
            "code" => 200,
            "message" => "anda telah sukses memperbarui credential.",
            "account" => $account,
            "secret" => $secret
        ];
    }

    /** 
     * Dapatkan variable yang dibutuhkan whapify pada file json
     * @return array
     * **/
    function getWhatsappSetting()
    {
        if (env('APP_ENV') == "production") {
            $data = $this->decryptConfig();
        } else {
            $content = Storage::disk($this->disk)->get($this->path . "/" . $this->filename);
            $data = json_decode($content, true);
        }

        return $data;
    }

    /**
     * Encripsi file whatsapp config menjadi whatsapp.key untuk menjaga akses akun tidak terbaca publik
     * File terdapat pada public path + configuration
     * @return bool
     */
    function encryptConfig()
    {
        if (env('APP_ENV') == "production")
            return [
                "code" => 500,
                "message" => "Anda telah memasuki mode produksi. jadi akses encripsi konfig telah dimatikan. mohon lakukan encript pada saat masih dalam status local."
            ];

        $config = $this->getWhatsappSetting();

        $confJson = json_encode($config);
        $this->setCredential($config['account'], $config['secret']);

        $cryptValue = Crypt::encryptString($confJson);
        Storage::disk($this->public_disk)->put($this->public_path . "/" . $this->public_name, $cryptValue);

        return [
            "code" => 200,
            "message" => "data telah berhasil dienkripsi"
        ];
    }

    /**
     * Encripsi file whatsapp config menjadi whatsapp.key untuk menjaga akses akun tidak terbaca publik
     * File terdapat pada public path + configuration
     * @return bool
     */
    function decryptConfig()
    {
        $exist = Storage::disk($this->public_disk)->exists($this->public_path . "/" . $this->public_name);

        if (!$exist)
            return [
                "code" => 404,
                "message" => "key file tidak ditemukan. mohon encript terlebih dahulu"
            ];

        $content = Storage::disk($this->public_disk)->get($this->public_path . "/" . $this->public_name);

        $data = Crypt::decryptString($content);

        return $data ? json_decode($data, true) : null;
    }

    /**
     * Tampilkan versi package
     * @return string
     */
    function getVersion()
    {
        return $this->version;
    }

    /**
     * Whapify API Class From Kayana
     * Initialisasi Credential akun whapify
     * @return array
     */
    function __Init($url, $datas = [])
    {
        $secret = $this->secret;
        $account = $this->account;

        // Define the API endpoint and payload
        $url =  $this->base_url . "/" . $url;
        $creds = [
            "secret" => $secret,
            "account" => $account,
        ];

        $data = array_merge($creds, $datas);

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the request
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $arr = json_decode($response, true);

        // Close cURL session
        curl_close($ch);

        return [
            "http_code" => $http_code,
            "response" => $arr
        ];
    }

    /** 
     * Whapify API Class From Kayana
     * Kirim Satu pesan ke satu penerima
     * 
     * @param penerima $recipient nomor penerima dalam bentuk +62000123999
     * @param isi_pesan $message pesan yang akan dikirim
     * @param tipe_pesan $type tipe pesan yang dikirim
     * @return array kode dan pesan
     * **/
    function send($recipient, $message, $type = "text")
    {
        $url = "send/whatsapp";
        $message = $message;
        $datas = [
            "recipient" => $recipient,
            "type" => $type,
            "message" => $message
        ];

        $re = $this->__Init($url, $datas);

        return $re['response'];
    }

    /** 
     * Whapify API Class From Kayana
     * Kirim Satu pesan ke satu penerima
     * 
     * @return array daftar pesan
     * **/
    function getMessages($status = "pending")
    {
        $url = "get/wa.";
        $re = $this->__Init($url . $status);

        return $re['response'];
    }

    /** 
     * Whapify API Class From Kayana
     * Kirim OTP via Whatsapp
     * 
     * @param recipient kepada siapa otp dikirim
     * @param message isi pesan didepan sebelum kode otp contoh : otp anda adalah 
     * @param type via apa otp ini dikirm sekarang hanya whatsapp yang didukung
     * @param expired waktu kadaluarta otp ini dalam detik, default 300 detik atau 5 menit
     * @return array kode dan data
     * **/
    function otp($recipient, $message = "Your OTP is", $type = "whatsapp", $expired = 300)
    {
        $url = "send/otp";
        $message = $message;
        $datas = [
            "phone" => $recipient,
            "type" => $type,
            "message" => $message . " {{otp}}",
            "expire" => $expired
        ];

        $re = $this->__Init($url, $datas);

        return $re['response'];
    }

    /** 
     * Whapify API Class From Kayana
     * Verifikasi nomor OTP yang dikirim via Whatsapp
     * 
     * @param otp nomor otp yang dikirimkan ke user
     * @return array kode dan data
     * **/
    function verifyOtp($otp)
    {
        $url = $this->base_url . "/" . "get/otp?secret=" . $this->secret . "&otp=" . $otp;

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the request
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Close cURL session
        curl_close($ch);

        return [
            "code" => $http_code,
            "datas" => $response
        ];
    }

    /** 
     * Whapify API Class From Kayana
     * Mendapatkan sis kredits whapify
     * 
     * @return array kode dan data
     * **/
    function getCredit()
    {
        $url = $this->base_url . "/" . "get/credits?secret=" . $this->secret;

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute the request
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Close cURL session
        curl_close($ch);

        return [
            "code" => $http_code,
            "datas" => json_decode($response, true)
        ];
    }

    /** 
     * Whapify API Class From Kayana
     * Mendapatkan sis kredits whapify
     * 
     * @return array kode dan data
     * **/
    function getMessageByID($id, $tipe = 'sent')
    {
        $url = $this->base_url . "/" . "get/wa.message?secret=" . $this->secret . "&type=" . $tipe . "&id=" .$id;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $arr = json_decode($response, true);

        curl_close($ch);

        return $arr;
    }
}
