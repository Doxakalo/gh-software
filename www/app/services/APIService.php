<?php

namespace App\Service;

class APIService
{

    public function authHeaderToken($token)
    {
        return "Authorization: Bearer " . $token;
    }
    public function authBasic($username, $password)
    {
        return "Authorization: Basic " . base64_encode($username . ":" . $password);
    }

    public function request($requestSettings, $data = null)
    {
        $headers = [];

        /* --- Init CURL --- */
        $ch = curl_init();

        /* --- Allow redirects --- */
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        /* --- Return response --- */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_HEADERFUNCTION,
            function ($curl, $header) use (&$headers) {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) // ignore invalid headers
                    return $len;

                $name = strtolower(trim($header[0]));
                if (!array_key_exists($name, $headers))
                    $headers[$name] = [trim($header[1])];
                else
                    $headers[$name][] = trim($header[1]);

                return $len;
            }
        );

        /* --- Return the transfer as a string --- */
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);

        /* --- Set headers --- */
        if (isset($requestSettings["headers"])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $requestSettings["headers"]);
        }

        if (isset($requestSettings["auth"])) {
            curl_setopt($ch, CURLOPT_USERPWD, $requestSettings["auth"]["user"] . ':' . $requestSettings["auth"]["password"]);
        }

        /* --- Set post data --- */
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, ($data === null ? "" : $data));
        }


        /* --- Set request method --- */
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestSettings["method"]);

        /* --- Set request URL --- */
        curl_setopt($ch, CURLOPT_URL, $requestSettings["api_url"] . $requestSettings["endpoint"]);


        /* --- Output--- */
        $result = curl_exec($ch);
        $errors = curl_error($ch);
        if (!empty($errors)) {
            return [
                "status" => curl_getinfo($ch),
                "headers" => $headers,
                "response" => $errors
            ];
        } else {
            return [
                "status" => curl_getinfo($ch),
                "headers" => $headers,
                "response" => json_decode($result, true)
            ];
        }
    }
}