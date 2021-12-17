<?php
defined('ABSPATH') or die();

/**
 * BS-Formular Plugin
 * @package Hummelt & Partner Gutenberg Block Plugin
 * Copyright 2021, Jens Wiecker
 * https://www.hummelt-werbeagentur.de/
 */
final class BsFormularUploadHandle
{
    private static $instance;
    protected array $opt;

    /**
     * @return static
     */
    public static function instance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->opt = [
            'mkdir_mode' => 0755,
            'file_type_end' => '/\.(.+$)/i',
            'accept_file_types' => '',
        ];
    }

    /**
     * @return object
     */
    public function initial(): object
    {
        $method = filter_input(INPUT_POST, 'method', FILTER_SANITIZE_STRING);
        return match ($method) {
            'add_file' => $this->upload_formular_file(),
            'delete_file' => $this->delete_formular_file(),
            default => (object)[],
        };
    }

    /**
     * @return object
     */
    private function upload_formular_file(): object
    {
        $response = new stdClass();
        $uploadDir = BS_FILE_UPLOAD_DIR;
        $response->status = false;

        if (empty($_FILES) && empty($_POST)) {
            $response->msg = 'Übertragungsfehler';
            return $response;
        }

        if (!wp_verify_nonce($_POST['_ajax_nonce'], 'bs_form_public_handle')) {
            $response->msg = 'Upload nicht erlaubt!';
            return $response;
        }

        $error = 0;
        $input_id = filter_input(INPUT_POST, 'input_field', FILTER_SANITIZE_STRING);
        $accept_mimes = filter_input(INPUT_POST, 'accept_mimes', FILTER_SANITIZE_STRING);
        $firstUpload = filter_input(INPUT_POST, 'firstUpload', FILTER_SANITIZE_NUMBER_INT);

        if ($firstUpload) {
            apply_filters('bs_form_destroy_dir', $uploadDir);
        }

        if (!$input_id) {
            $error++;
        }
        if (!$accept_mimes) {
            $error++;
        }

        if ($error) {
            $response->msg = 'Übertragungsfehler, Daten unvollständig!';
            return $response;
        }

        $file_id = apply_filters('get_bs_form_generate_random', 8, 0, 4);
        $response->file_id = $file_id;

        $file = $_FILES[key($_FILES)];
        $folderName = $input_id;
        $upload_dir = BS_FILE_UPLOAD_DIR . $folderName . DIRECTORY_SEPARATOR;

        //Create Folder
        if (!$this->check_upload_path($upload_dir)) {
            $response->msg = 'Verzeichnis konnte nicht erstellt werden!';
            return $response;
        }

        //Check Max Files
        $uplCount = $this->check_upload_count($upload_dir);
        if ($uplCount >= get_option('upload_max_files')) {
            $response->msg = 'Maximal ' . get_option('upload_max_files') . ' zulässig!';
            return $response;
        }

        $tempFile = $file['tmp_name'];
        $fileOrginalName = $this->trim_file_name($file['name']);
        $fileName = $file_id . '#' . $fileOrginalName;

        // Check Name
        $uplName = $this->check_upload_name($upload_dir, $fileOrginalName);
        if ($uplName) {
            $response->msg = 'Datei "' . $uplName . '" schon vorhanden!';
            return $response;
        }

        // Check Types
        $accept_files = str_replace(' ', '', $accept_mimes);
        $accept_files = str_replace(',', '|', $accept_files);
        if (!preg_match('/\.(' . $accept_files . ')$/i', $fileName)) {
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

        // Check FileSize
        $dbFileSize = get_option('file_max_size') * 1024 * 1024;
        $fileSize = $this->get_file_size($upload_dir . $fileName);
        if ($fileSize > $dbFileSize) {
            $response->msg = 'File zu groß! ( max: ' . $this->FileSizeConvert($dbFileSize) . ' )';
            unlink($dest);
            return $response;
        }

        // Check MimeTypes
        $fileType = $this->check_upload_mime_type($upload_dir . $fileName);
        $regEx = '@/(.+)@i';
        preg_match($regEx, $fileType, $matches);
        if (!$matches) {
            unlink($dest);
            $response->msg = 'Format wird nicht unterstützt!';
            return $response;
        }

        $accept_types = preg_replace("/\s+/", "", $accept_mimes);
        $accept_types = str_replace([',', ';', '-'], '#', $accept_types);
        $accept_types = explode('#', $accept_types);

        if (!in_array($matches[1], $accept_types)) {
            $response->msg = strtoupper($matches[1]) . ' nicht erlaubt!';
            unlink($dest);
            return $response;
        }

        $response->status = true;
        $response->input_id = $input_id;
        $response->file = $fileName;

        return $response;
    }

    /**
     * @return object
     */
    private function delete_formular_file(): object
    {
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
        $file_name = filter_input(INPUT_POST, 'file_name', FILTER_SANITIZE_STRING);

        if (!$input_id || !$file_name) {
            $response->msg = 'Übertragungsfehler, Daten unvollständig!';
            return $response;
        }

        $uploadDir = BS_FILE_UPLOAD_DIR . $input_id . DIRECTORY_SEPARATOR;

        $x = 0;
        if(is_dir($uploadDir)) {
            foreach (scandir($uploadDir) as $file) {
                if ($file == "." || $file == "..")
                    continue;
                if (preg_match("/.{9}($file_name)$/i", $file)) {
                    if (is_file($uploadDir . $file)) {
                        unlink($uploadDir . $file);
                        $x++;
                    }
                }
            }
        }
        if (!$x) {
           // $response->msg = 'Datei wurde nicht gefunden!';
           // return $response;
        }

        $response->status = true;
        return $response;
    }

    /**
     * @param $dir
     * @return int
     */
    private function check_upload_count($dir): int
    {
        $x = 0;
        foreach (scandir($dir) as $file) {
            if ($file == "." || $file == "..")
                continue;
            $x++;
        }
        return $x;
    }

    /**
     * @param $dir
     * @param $filename
     * @return string
     */
    private function check_upload_name($dir, $filename): string
    {
        $return = '';
        $regEx = '/.{9}(' . $filename . ')$/i';
        foreach (scandir($dir) as $file) {
            if ($file == "." || $file == "..")
                continue;
            preg_match($regEx, $file, $matches);
            if ($matches) {
                $return = $matches[1];
            }
        }

        return $return;
    }

    /**
     * @param $name
     * @return string
     */
    protected function trim_file_name($name): string
    {
        $name = trim($this->basename(stripslashes($name)), ".\x00..\x20");
        if (!$name) {
            $name = str_replace('.', '-', microtime(true));
        }
        return $name;
    }

    /**
     * @param $file
     * @return string
     */
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
    protected function set_header($header): void
    {
        @header("{$header}");
    }

    /**
     * @param $file_path
     * @param false $clear_stat_cache
     * @return float
     */
    protected function get_file_size($file_path, bool $clear_stat_cache = false): float
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

    /**
     * @param $size
     * @return float
     */
    protected function fix_integer_overflow($size): float
    {
        if ($size < 0) {
            $size += 2.0 * (PHP_INT_MAX + 1);
        }
        return $size;
    }

    /**
     * @param $path
     * @return bool
     */
    protected function check_upload_path($path): bool
    {
        if (!is_dir($path)) {
            if (!mkdir($path, 0777, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param float $bytes
     * @return string
     */
    protected function FileSizeConvert(float $bytes): string
    {
        $result = '';
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



