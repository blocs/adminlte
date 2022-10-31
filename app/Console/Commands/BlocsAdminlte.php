<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BlocsAdminlte extends Command
{
    protected $signature = 'blocs:adminlte';
    protected $description = 'Deploy blocs/adminlte package';

    private static $root_dir;
    private static $stub_dir;

    public function __construct()
    {
        self::$root_dir = str_replace(DIRECTORY_SEPARATOR, '/', realpath(dirname(__FILE__).'/../../../../../../'));
        self::$stub_dir = str_replace(DIRECTORY_SEPARATOR, '/', realpath(dirname(__FILE__).'/../../../'));

        parent::__construct();
    }

    public function handle()
    {
        /* アップデート状況把握のため更新情報を取得 */

        $file_loc = self::$root_dir.'/storage/blocs_update.json';
        if (is_file($file_loc)) {
            $update_json_data = json_decode(file_get_contents($file_loc), true);
        } else {
            $update_json_data = [];
        }

        /* ディレクトリを配置 */

        foreach (['public', 'resources'] as $target_dir) {
            $update_json_data = self::_copy_dir(self::$stub_dir.'/'.$target_dir, self::$root_dir.'/'.$target_dir, $update_json_data);
            echo <<< END_of_TEXT
Copy "{$target_dir}"

END_of_TEXT;
        }

        ksort($update_json_data);
        file_put_contents($file_loc, json_encode($update_json_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) && chmod($file_loc, 0666);
    }

    /* Private function */

    private static function _copy_dir($dir_name, $new_dir, $update_json_data)
    {
        is_dir($new_dir) || mkdir($new_dir, 0777, true) && chmod($new_dir, 0777);

        if (!(is_dir($dir_name) && $files = scandir($dir_name))) {
            return $update_json_data;
        }

        foreach ($files as $file) {
            if ('.' == substr($file, 0, 1) && '.gitkeep' != $file && '.htaccess' != $file) {
                continue;
            }

            if (is_dir($dir_name.'/'.$file)) {
                $update_json_data = self::_copy_dir($dir_name.'/'.$file, $new_dir.'/'.$file, $update_json_data);
            } else {
                $update_json_data = self::_copy_file($dir_name.'/'.$file, $new_dir.'/'.$file, $update_json_data);
            }
        }

        return $update_json_data;
    }

    private static function _copy_file($original_file, $target_file, $update_json_data)
    {
        $original_file = str_replace(DIRECTORY_SEPARATOR, '/', realpath($original_file));
        $new_contents = file_get_contents($original_file);
        $file_key = substr($target_file, strlen(self::$root_dir));

        if (!is_file($target_file) || !filesize($target_file)) {
            // コピー先にファイルがない
            if (!empty($update_json_data[$file_key])) {
                // ファイルを意図的に消した時はコピーしない
                return $update_json_data;
            }

            file_put_contents($target_file, $new_contents) && chmod($target_file, 0666);
            $target_file = str_replace(DIRECTORY_SEPARATOR, '/', realpath($target_file));
            $update_json_data[$file_key] = md5($new_contents);

            return $update_json_data;
        }

        // コピー先にファイルがある
        $target_file = str_replace(DIRECTORY_SEPARATOR, '/', realpath($target_file));
        $old_contents = file_get_contents($target_file);

        if ($new_contents === $old_contents) {
            // ファイルが更新されていない
            $update_json_data[$file_key] = md5($new_contents);

            return $update_json_data;
        }

        if (isset($update_json_data[$file_key]) && $update_json_data[$file_key] === md5($old_contents)) {
            // ファイルが更新された
            file_put_contents($target_file, $new_contents) && chmod($target_file, 0666);
            $update_json_data[$file_key] = md5($new_contents);

            return $update_json_data;
        }

        // 違う内容のファイルがある
        echo <<< END_of_TEXT
\e[7;31m"{$target_file}" already exists.\e[m

END_of_TEXT;

        return $update_json_data;
    }
}
