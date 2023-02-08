<?php

class utils
{

    public $_allow = [];

    public $_content_type = "application/json";

    public $_request = [];

    private $_method = "";

    private $_code = 200;

    private $_model;

    public function __construct()
    {
        $this->inputs();
    }


    public function ObtenerIP()
    {
        if ($_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
            $client_ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : ((!empty($_ENV['REMOTE_ADDR'])) ? $_ENV['REMOTE_ADDR'] : "unknown");

            $entries = preg_split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);

            reset($entries);
            while (list(, $entry) = each($entries)) {
                $entry = trim($entry);
                if (preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $entry, $ip_list)) {
                    $private_ip = [
                        '/^0\./',
                        '/^127\.0\.0\.1/',
                        '/^192\.168\..*/',
                        '/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/',
                        '/^10\..*/',
                    ];

                    $found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);

                    if ($client_ip != $found_ip) {
                        $client_ip = $found_ip;
                        break;
                    }
                }
            }
        } else {
            $client_ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : ((!empty($_ENV['REMOTE_ADDR'])) ? $_ENV['REMOTE_ADDR'] : "unknown");
        }

        return $client_ip;
    }

    public function json_response($data, $status)
    {
        echo json_encode([$data, $status]);
        exit();
    }


    public function get_referer()
    {
        return $_SERVER['HTTP_REFERER'];
    }

    public function response($data, $status)
    {
        $this->_code = ($status) ? $status : 200;
        $this->set_headers();
        echo $data;
        exit;
    }

    // For a list of http codes checkout http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
    private function get_status_message()
    {
        $status = [
            200 => 'OK',
            201 => 'Created',
            204 => 'No Content',
            302 => 'Found',
            400 => 'Bad Request',
            401 => 'There is no data',
            404 => 'Not Found',
            406 => 'Not Acceptable',
            500 => 'Internal Server Error',
        ];

        return ($status[$this->_code]) ? $status[$this->_code] : $status[500];
    }

    public function get_request_method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    private function inputs()
    {
        switch ($this->get_request_method()) {
            case "POST":
                $this->_request = $this->cleanInputs($_POST);
                break;
            case "GET":
            case "DELETE":
                $this->_request = $this->cleanInputs($_GET);
                break;
            case "PUT":
                parse_str(file_get_contents("php://input"), $this->_request);
                $this->_request = $this->cleanInputs($this->_request);
                break;
            default:
                $this->response('', 406);
                break;
        }
    }

    private function cleanInputs($data)
    {
        $clean_input = [];
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->cleanInputs($v);
            }
        } else {
            if (get_magic_quotes_gpc()) {
                $data = trim(stripslashes($data));
            }
            $data        = strip_tags($data);
            $clean_input = trim($data);
        }

        return $clean_input;
    }

    private function set_headers()
    {
        header("HTTP/1.1 " . $this->_code . " " . $this->get_status_message());
        header("Content-Type:" . $this->_content_type);
    }

    public function json($data)
    {
        if (is_array($data)) {
            return json_encode($data);
        }
    }

    public function quitar_tildes($cadena)
    {
        //echo 'recibi cadena'.$cadena;
        $no_permitidas = [
            "á",
            "é",
            "í",
            "ó",
            "ú",
            "Á",
            "É",
            "Í",
            "Ó",
            "Ú",
            "ñ",
            "À",
            "Ã",
            "Ì",
            "Ò",
            "Ù",
            "Ã™",
            "Ã ",
            "Ã¨",
            "Ã¬",
            "Ã²",
            "Ã¹",
            "ç",
            "Ç",
            "Ã¢",
            "ê",
            "Ã®",
            "Ã´",
            "Ã»",
            "Ã‚",
            "ÃŠ",
            "ÃŽ",
            "Ã”",
            "Ã›",
            "ü",
            "Ã¶",
            "Ã–",
            "Ã¯",
            "Ã¤",
            "«",
            "Ò",
            "Ã",
            "Ã„",
            "Ã‹",
        ];
        $permitidas    = [
            "a",
            "e",
            "i",
            "o",
            "u",
            "A",
            "E",
            "I",
            "O",
            "U",
            "n",
            "N",
            "A",
            "E",
            "I",
            "O",
            "U",
            "a",
            "e",
            "i",
            "o",
            "u",
            "c",
            "C",
            "a",
            "e",
            "i",
            "o",
            "u",
            "A",
            "E",
            "I",
            "O",
            "U",
            "u",
            "o",
            "O",
            "i",
            "a",
            "e",
            "U",
            "I",
            "A",
            "E",
        ];
        $texto         = str_replace($no_permitidas, $permitidas, $cadena);
        return $texto;
    }

}
