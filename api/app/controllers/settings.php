<?php
namespace controllers;
use core\image;
use models\settings as SettingModel;

class settings extends authorized
{
    public function update()
    {
        $settings = new SettingModel();
        $settings->update(
            $this->requestFiltered([
                'email',
                'phone',
                'gtm_head',
                'gtm_body',
                'google',
                'yandex',
                'robots',
                'vk',
                'twitter',
                'ok',
                'youtube',
                'telegram',
                'facebook',
                'google_plus',
                'instagram',
                'address',
                'coordinates',
                'not_found_description',
                'not_found_title',
                'not_found_desc',
                'not_found_keywords',
                'sender_name',
                'sender_email',
                'sender_password',
                'sender_host',
                'sender_port'
            ]));
        $result = $this->getAll();
        return $this->updateRobots($result['robots']);
    }

    public function getAll()
    {
        $settings = new SettingModel();
        return $settings->getAll();
    }

    public function updateRobots($text)
    {
        $target_dir = ROOT.'/public/';

        if (is_writable($target_dir)){
            $fp = fopen($target_dir.'robots.txt', 'w+');
            if (!$fp){
                return ['error' => 'Не удалось открыть файл robots.txt'];
            }
            if (fwrite($fp, $text) === false){
                return ['error' => 'Не удалось записать файл robots.txt'];
            }
            fclose($fp);
            return true;
        } else {
            if ( chmod($target_dir, 0775) ){
                $this->updateRobots($text);
            } else {
                return ['error' => 'Не удалось задать права на запись папки'];
            }
        }
    }

    public function redactorUpload()
    {
        $img_dir = '/redactor/settings';

        if (!is_dir(ROOT.'/public/media'.$img_dir)) {
            mkdir(ROOT.'/public/media'.$img_dir, 0775, true);
        }

        if ($this->files->file['name']) {

            $extension = strtolower(strrchr($this->files->file['name'], '.'));
            $this->files->file['name'] = md5_file($this->files->file['tmp_name']).$extension;

            if (move_uploaded_file($this->files->file['tmp_name'], ROOT.'/public/media'.$img_dir.'/'.$this->files->file['name'])) {

                $path = ROOT.'/public/media'.$img_dir.'/'.$this->files->file['name'];
                $image = new image($path);
                $image->resizeIfBigger(1920, 1080);
                $image->save($path, "85");
                unset($image);

                return [
                    'filelink' => '/media'.$img_dir.'/'.$this->files->file['name']
                ];
            }

        }
    }
}