<?php

class VKPoster
{
    private $access_token = '';

    private $user_id = '';

    private $group_id = '';


    public function __construct($access_token)
    {
        if ($access_token)
            $this->access_token = $access_token;
        else
            die();
    }

    public function setUserIDVK($user_id)
    {
        $this->user_id = $user_id;
    }

    public function setGroupIDVK($group_id)
    {
        $this->group_id = $group_id;
    }

    public function setAccessToken($access_token)
    {
        $this->access_token = $access_token;
    }

    private function excuteMethod($method, $data = array())
    {
        $data['access_token'] = $this->access_token;

        $data = http_build_query($data);

        $context_options = array(
            'http' => array(
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n"
                    . "Content-Length: " . strlen($data) . "\r\n",
                'content' => $data
            )
        );

        $context = stream_context_create($context_options);

        $result = file_get_contents(
            "https://api.vk.com/method/$method",
            false,
            $context);

        $result = json_decode($result);
        return $result->response;
    }

    /*
     * Используеться для передачи POST данных на сервер ВК
     * после использования подфункции getUploadServer для получения
     * ссылки на загрузку, фото, аудио, видео, документов
     * Только этот код работает для загрузки
     * все остальные методы вызываються file_get_contents в функции 
     */
    private function putToServer($url, $data = array())
    {
        $data['access_token'] = $this->access_token;

        $curl = curl_init(null);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($result);
        return $response;
    }

    private function  photosGetWallUploadServer($to_user = true)
    {
        if (!$to_user)
            $parameters = array('group_id' => $this->group_id);
        $response = $this->excuteMethod('photos.getWallUploadServer', $parameters);
        return $response->upload_url;
    }


    private function photosGetProfileUploadServer()
    {
        $response = $this->excuteMethod('photos.getProfileUploadServer');
        return $response->upload_url;
    }

    /**
     * Загружает фото на сервер для публикации в группе или на личной стене
     * @param $img - фото физически расположенное на сервере - $_SERVER['DOCUMENT_ROOT'] . '/img/photo.jpg'
     * @param $to_user boolean(true) - загрузить на стену пользователя или группы
     *
     */
    public function photosSaveWallPhoto($img, $to_user = true)
    {
        if (file_exists($img)) {
            // Полуим URL для загрузки изображения на сервер
            $url = $this->photosGetWallUploadServer($to_user);

            // @ - обязателен, зачем так и не понял но без него ничего не происходит!
            $data['photo'] = '@' . $img;
            // Загружаем фото на срвер
            $response = $this->putToServer($url, $data);
            /*
             * Возвращает массив данных для загрузки фото на сервер
             * Отладочная информация для понимания принципа работы
            (
                    [server] => 409523
                    [photo] => [{"photo":"9b8d1c0aeb:x","sizes":[["s","409523162","60af","YvOnkWpir30",75,44],["m","409523162","60b0","oa6UtUUTpuY",130,77],["x","409523162","60b1","PFrA1UMqkvA",560,330],["o","409523162","60b2","hhzkQkcpwOc",130,87],["p","409523162","60b3","L9JlMAOfDs0",200,133],["q","409523162","60b4","FkaqlkJ3BnA",320,213],["r","409523162","60b5","scHqdOIkESk",510,330]],"kid":"554849f71e5978dbbff4f7a10ca0dc04"}]
                    [hash] => 7237f741817b50778432517219d3116d
                )
             */
            //print_r('<pre>');
            //print_r($response);
            //print_r('</pre>');

            // Загруженное изображение сохраняем на сервере
            if ($to_user)
                $parameters['user_id'] = $this->user_id;
            else
                $parameters['group_id'] = $this->group_id;

            $parameters['server'] = $response->server;
            $parameters['photo'] = $response->photo;
            $parameters['hash'] = $response->hash;
            $result = $this->excuteMethod('photos.saveWallPhoto', $parameters);
            $response = $result[0];
            //print_r('<pre>');
            //print_r($response);
            //print_r('</pre>');
            /*
             * После использования метода photos.saveWallPhoto, результат выполнения
             * далее [id] => photo1428162_317485310 можно использовать для публикации на личную стену
             * или на стену в группе
             *
             stdClass Object
                (
                    [pid] => 317485310
                    [id] => photo1428162_317485310
                    [aid] => -14
                    [owner_id] => 1428162
                    [src] => http://cs409523.vk.me/v409523162/60b7/kdHYuYG5lWE.jpg
                    [src_big] => http://cs409523.vk.me/v409523162/60b8/7zfZ1KWqp-c.jpg
                    [src_small] => http://cs409523.vk.me/v409523162/60b6/ub9ibvF3loA.jpg
                    [width] => 560
                    [height] => 330
                    [text] =>
                    [created] => 1388126782
                )
             */
            return $response;
        } else {
            echo 'ERROR: file does not exist';
            die();
        }
    }

    /**
     * @param string $id_img
     * @param string $text
     * @param bool $to_user
     *
     *
     *
     * @param 1 — запись будет доступна только друзьям, 0 — всем пользователям. По умолчанию публикуемые записи доступны всем пользователям.
     */
    public function wallPost($data = array())
    {
        $defaults = array(
            'owner_id' => 1,
            'friends_only' => 0,
            'from_group' => 1,
            'message' => '',
            'attachments' => '',
        );

        $parameters = array_merge($defaults, $data);

        //print_r($parameters);

        if (empty($parameters['message'])) {
            if (empty($parameters['attachments'])) {
                echo 'ERROR: message empty';
                die();
            }
        }

        if ($parameters['owner_id'] == 1)
            $parameters['owner_id'] = $this->user_id;
        else
            $parameters['owner_id'] = '-' . $this->group_id;

        return $this->excuteMethod('wall.post', $parameters);
    }

}
