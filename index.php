<?php
require_once './vendor/autoload.php';

const VK_TOKEN = 'ae51ff9f59f0c40f029c67c3588466bcec1f7ff0f333404769952a31290c3cf097f5b7c0cfd90b0da4562';

function myLog($str) {
    file_put_contents("php://stdout", "$str\n");
}

const COLOR_NEGATIVE = 'negative';
const COLOR_POSITIVE = 'positive';
const COLOR_DEFAULT = 'default';
const COLOR_PRIMARY = 'primary';
const CMD_ID = 'ID';
const CMD_NEXT = 'NEXT';
const CMD_TYPING = 'TYPING';

function getBtn($label, $color, $payload = '') {
    return [
        'action' => [
            'type' => 'text',
            "payload" => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'label' => $label
        ],
        'color' => $color
    ];
}
$json = file_get_contents('php://input');
//myLog($json);
$data = json_decode($json, true);
$type = $data['type'] ?? '';
$vk = new VKApiClient('5.78', VKLanguage::RUSSIAN);
if ($type === 'message_new') {
    $message = $data['object'] ?? [];
    $userId = $message['from_id'] ?? 0; //this need to change
    $body = $message['body'] ?? '';
    $payload = $message['payload'] ?? '';
    if ($payload) {
        $payload = json_decode($payload, true);
    }
    //myLog($userId);
   myLog("MSG: ".$body." PAYLOAD:".$payload);
    $kbd = [
        'one_time' => false,
        'buttons' => [
            [getBtn("Покажи мой ID", COLOR_DEFAULT, CMD_ID)],
            [getBtn("Далее", COLOR_PRIMARY, CMD_NEXT)],
        ]
    ];
    $msg = "Привет я бот!";
    if ($payload === CMD_ID) {
        $msg = "Ваш id ".$userId;
    }
    if ($payload === CMD_NEXT) {
        $kbd = [
            'one_time' => false,
            'buttons' => [
                [getBtn("Пошли тайпинг", COLOR_POSITIVE, CMD_TYPING)],
                [getBtn("Назад", COLOR_NEGATIVE)],
            ]
        ];
    }
    if ($payload === CMD_TYPING) {
        try {
            $res = $vk->messages()->setActivity(VK_TOKEN, [
                'peer_id' => $userId,
                'type' => 'typing'
            ]);
            $msg = null;
        } catch (\Exception $e) {
            myLog( $e->getCode().' '.$e->getMessage() );
        }
    }
    try {
        if ($msg !== null) {
            $response = $vk->messages()->send(VK_TOKEN, [
                'peer_id' => $userId,
                'message' => $msg,
                'keyboard' => json_encode($kbd, JSON_UNESCAPED_UNICODE)
            ]);
        }
    } catch (\Exception $e) {
        myLog( $e->getCode().' '.$e->getMessage() );
    }
}
echo  "OK";