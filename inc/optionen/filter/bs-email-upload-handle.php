<?php
defined( 'ABSPATH' ) or die();
/**
 * BS-Formular Plugin
 * @package Hummelt & Partner Gutenberg Block Plugin
 * Copyright 2021, Jens Wiecker
 * https://www.hummelt-werbeagentur.de/
 */
//@header('Cache-Control: post-check=0, pre-check=0', false);


final class BsFormularUploadHandle {
    private static $instance;
    protected array $opt;

    /**
     * @return static
     */
    public static function instance(): self {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->opt = [
            'mkdir_mode' => 0755,
            'file_type_end' => '/\.(.+$)/i',
            'accept_file_types' =>'',
        ];
    }

    public function initial():object{

        $method = filter_input(INPUT_POST, 'method', FILTER_SANITIZE_STRING);

        switch ($method) {
            case 'add_file':
                return $this->upload_formular_file();
            case 'delete_file':
                return $this-> delete_formular_file();
        }

        return (object) [];
    }

    private function upload_formular_file():object {
        $response = new stdClass();
        $uploadDir = BS_FILE_UPLOAD_DIR;
        $response->status = false;
        $record = new stdClass();

        if (empty($_FILES) && empty($_POST)) {
            $response->msg = 'Übertragungsfehler';
             return $response;
        }

          if (!wp_verify_nonce($_POST['_ajax_nonce'], 'bs_form_public_handle')) {
              $response->msg = 'Upload nicht erlaubt!';
              return $response;
          }

          $input_id = filter_input(INPUT_POST, 'input_field', FILTER_SANITIZE_STRING);
          $accept_mimes = filter_input(INPUT_POST, 'accept_mimes', FILTER_SANITIZE_STRING);
          $file_id = filter_input(INPUT_POST, 'pond_id', FILTER_SANITIZE_STRING);
          if(!$input_id || !$accept_mimes || !$file_id) {
              $response->msg = 'Übertragungsfehler, Daten unvollständig!';
              return $response;
          }

          $response->file_id = $file_id;

          $file = $_FILES[key($_FILES)];
          $folderName = $input_id;
          $upload_dir = BS_FILE_UPLOAD_DIR . $folderName . DIRECTORY_SEPARATOR;

          if(!$this->check_upload_path($upload_dir)) {
              $response->msg = 'Verzeichnis konnte nicht erstellt werden!';
              return $response;
          }

          $tempFile = $file['tmp_name'];
          $fileName = $this->trim_file_name($file['name']);
          $fileName = $file_id.'#'.$fileName;

          $accept_files = str_replace(' ','', $accept_mimes);
          $accept_files = str_replace(',','|', $accept_files);
          if (!preg_match('/\.('.$accept_files.')$/i', $fileName)) {
              preg_match($this->opt['file_type_end'], $fileName, $matches, PREG_OFFSET_CAPTURE, 0);

              $response->msg = strtoupper($matches[1][0]) . ' nicht erlaubt!';
              return $response;
          }

          $dest = $upload_dir . $fileName;
          if (move_uploaded_file($tempFile, $dest)) {
              unset($tempFile);
          } else {
              $response->msg = 'Datei-Upload fehlgeschlagen!';
              return $response;
          }

           $dbFileSize = get_option('file_max_size') * 1024 * 1024;
           $fileSize =  $this->get_file_size($upload_dir . $fileName);
           if($fileSize > $dbFileSize){
               $response->msg = 'File zu groß! (max: '.$this->FileSizeConvert($dbFileSize).')';
               return $response;
           }

           $fileType =  $this->check_upload_mime_type($upload_dir . $fileName);
           $regEx = '@/(.+)@i';
           preg_match($regEx, $fileType, $matches);
           if(!$matches){
               $response->msg = 'Format wird nicht unterstützt!';
               return $response;
           }

          $accept_types = preg_replace("/\s+/", "", $accept_mimes);
          $accept_types = str_replace([',',';','-'],'#', $accept_types);
          $accept_types = explode('#', $accept_types);

          if(!in_array($matches[1], $accept_types)){
              $response->msg = strtoupper($matches[1]) . ' nicht erlaubt!';
              return $response;
          }

          $response->file_size = $this->FileSizeConvert($fileSize);
          $response->status = true;
          $response->input_id = $input_id;
          $response->file = $fileName;
          return $response;
    }

    private function delete_formular_file():object {
        $response = new stdClass();
        $response->status = false;
        if (!$_POST) {
            $response->msg = 'Übertragungsfehler';
            return $response;
        }

        if (!wp_verify_nonce($_POST['_ajax_nonce'], 'bs_form_public_handle')) {
            $response->msg = 'Upload nicht erlaubt!';
            return $response;
        }

        $input_id = filter_input(INPUT_POST, 'input_field', FILTER_SANITIZE_STRING);
        $file_id = filter_input(INPUT_POST, 'pond_id', FILTER_SANITIZE_STRING);

        if(!$input_id || !$file_id) {
            $response->msg = 'Übertragungsfehler, Daten unvollständig!';
            return $response;
        }

        $uploadDir = BS_FILE_UPLOAD_DIR . $input_id . DIRECTORY_SEPARATOR;

        $x = 0;
        foreach (scandir($uploadDir) as $file) {
            if ($file == "." || $file == "..")
            continue;
            if (preg_match("/($file_id?#)/i", $file)) {
                unlink($uploadDir . $file);
                $x++;
                break;
            }
        }

        if(!$x) {
            $response->msg = 'Datei wurde nicht gefunden!';
            return $response;
        }

        $response->status = true;
        return $response;
    }

    /**
     * @param $name
     * @return string
     */
    protected function trim_file_name($name):string
    {
        $name = trim($this->basename(stripslashes($name)), ".\x00..\x20");
        if (!$name) {
            $name = str_replace('.', '-', microtime(true));
        }
        return $name;
    }

    protected function check_upload_mime_type($file): string
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($file);
    }

    /**
     * @param string $filepath
     * @param string $suffix
     * @return string
     */
    protected function basename(string $filepath, string $suffix = ''): string
    {
        $splited = preg_split('/\//', rtrim($filepath, '/ '));
        return substr(basename('X' . $splited[count($splited) - 1], $suffix), 1);
    }

    /**
     * @param $header
     */
    protected function set_header($header):void
    {
        @header("{$header}");
    }

    protected function get_file_size($file_path, $clear_stat_cache = false): float
    {
        if ($clear_stat_cache) {
            if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                clearstatcache(true, $file_path);
            } else {
                clearstatcache();
            }
        }
        return $this->fix_integer_overflow(filesize($file_path));
    }

    protected function fix_integer_overflow($size): float
    {
        if ($size < 0) {
            $size += 2.0 * (PHP_INT_MAX + 1);
        }
        return $size;
    }

    protected function check_upload_path($path): bool
    {
        if(!is_dir($path)){
            if (!mkdir($path, 0777, true)) {
                return false;
            }
        }
        return true;
    }

    protected function FileSizeConvert(float $bytes): string {
        $result = '';
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array("UNIT" => "TB", "VALUE" => pow(1024, 4)),
            1 => array("UNIT" => "GB", "VALUE" => pow(1024, 3)),
            2 => array("UNIT" => "MB", "VALUE" => pow(1024, 2)),
            3 => array("UNIT" => "KB", "VALUE" => 1024),
            4 => array("UNIT" => "B", "VALUE" => 1),
        );

        foreach ($arBytes as $arItem) {
            if ($bytes >= $arItem["VALUE"]) {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", ",", strval(round($result, 2))) . " " . $arItem["UNIT"];
                break;
            }
        }
        return $result;
    }
}



