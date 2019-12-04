<?php

namespace omgdef\yii\unisender\mailer;

use omgdef\unisender\UniOneWrapper;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\mail\BaseMailer;

class UniOneMailer extends BaseMailer
{
    /**
     * @var string message default class name.
     */
    public $messageClass = 'omgdef\yii\unisender\mailer\Message';
    /**
     * @var string
     */
    public $defaultMailerName = 'Mailer';
    /**
     * @var string
     */
    public $uniOneComponent = 'uniOne';

    /**
     * @inheritdoc
     */
    protected function sendMessage($message)
    {
        $email = $message->getTo();
        if (is_array($email)) {
            if (ArrayHelper::isAssociative($email)) {
                $addresses = [];
                foreach ($email as $address => $name) {
                    $addresses[] = [
                        'email' => trim($address),
                        'substitutions' => [
                            'to_name' => trim($name),
                        ]
                    ];
                }
                $email = $addresses;
            } else {
                $addresses = [];
                foreach ($email as $address) {
                    $addresses[] = [
                        'email' => trim($address),
                    ];
                }
                $email = $addresses;
            }
        } else {
            $email = [
                [
                    'email' => trim($email),
                ]
            ];
        }

        $from = $message->getFrom();

        $senderName = null;
        $senderEmail = null;

        if (is_array($from)) {
            $count = count(array_keys($from));

            if ($count > 1 && ($count !== count($email))) {
                throw new InvalidConfigException('Unisender requires email, sender_name and sender_email have equal length or have only one element');
            }

            if (!ArrayHelper::isAssociative($from)) {
                throw new InvalidConfigException('You should use associative array for the "from" attribute');
            }

            foreach ($from as $address => $name) {
                $senderName = $name;
                $senderEmail = $address;
                break;
            }
        } else {
            $senderName = $this->defaultMailerName;
            $senderEmail = $from;
        }

        $replyTo = $message->getReplyTo(); // Pass it to headers
        if (is_array($replyTo)) {
            if (ArrayHelper::isAssociative($from)) {
                $replyTo = array_keys($replyTo);
            }

            $replyTo = implode(';', $replyTo);
        }

        $headers = [];
        if ($replyTo) {
            $headers['X-ReplyTo'] = $replyTo;
        }

        $attachments = $message->getAttachments();
        foreach ($attachments as &$attachment) {
            $attachment['content'] = base64_encode($attachment['content']);
        }

        $embed = $message->getEmbed();
        foreach ($embed as &$embedItem) {
            $embedItem['content'] = base64_encode($embedItem['content']);
        }

        $requestBody = [
            'message' => [
                'body' => [
                    'html' => $message->getHtmlBody(),
                    'plaintext' => $message->getTextBody(),
                ],
                'subject' => $message->getSubject(),
                'from_email' => $senderEmail,
                'from_name' => $senderName,
                'headers' => $headers,
                'recipients' => $email,
                'attachments' => $attachments,
                'inline_attachments' => $embed,
            ],
        ];

        /**
         * @var $uniSender UniOneWrapper
         */
        $uniSender = Yii::$app->get($this->uniOneComponent);
        $response = $uniSender->send($requestBody);

        return isset($response['status']) && $response['status'] === 'success';
    }
}
