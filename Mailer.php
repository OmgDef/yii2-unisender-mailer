<?php
namespace omgdef\yii\unisender\mailer;

use omgdef\unisender\UniSenderWrapper;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\mail\BaseMailer;

class Mailer extends BaseMailer
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
     * @var integer
     */
    public $listID;
    /**
     * @var string
     */
    public $uniSenderComponent = 'uniSender';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->listID || !is_int($this->listID)) {
            throw new InvalidConfigException('You should specify mailing list');
        }
        parent::init();
    }

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
                    $addresses[] = trim($name) . " " . trim($address);
                }
                $email = $addresses;
            }
        }

        $from = $message->getFrom();

        $senderName = [];
        $senderEmail = [];

        if (is_array($from)) {
            $count = count(array_keys($from));

            if ($count > 1 && ($count !== count($email))) {
                throw new InvalidConfigException('Unisender requires email, sender_name and sender_email have equal length or have only one element');
            }

            if (!ArrayHelper::isAssociative($from)) {
                throw new InvalidConfigException('You should use associative array for the "from" attribute');
            }

            foreach ($from as $address => $name) {
                $senderName[] = $name;
                $senderEmail[] = $address;
            }
        } else {
            $senderName[] = $this->defaultMailerName;
            $senderEmail[] = $from;
        }

        $replyTo = $message->getReplyTo(); // Pass it to headers
        if (is_array($replyTo)) {
            if (ArrayHelper::isAssociative($from)) {
                $replyTo = array_keys($replyTo);
            }

            $replyTo = implode(';', $replyTo);
        }

        $cc = $message->getCc();

        if (is_array($cc)) {
            if (ArrayHelper::isAssociative($from)) {
                $cc = array_keys($cc);
            }

            $cc = implode(',', $cc);
        }

        $requestBody = [
            'email' => $email,
            'sender_name' => count($senderName) === 1 ? $senderName[0] : $senderName,
            'sender_email' => count($senderEmail) === 1 ? $senderEmail[0] : $senderEmail,
            'subject' => $message->getSubject(),
            'body' => $message->getBody(),
            'list_id' => $this->listID,
            'сс' => $cc,
        ];

        if ($replyTo) {
            $requestBody['headers'] = "Reply-To: {$replyTo}\n";
        }

        $attachments = $message->getAttachments();
        if ($attachments) {
            $requestBody['attachments'] = $attachments;
        }

        /**
         * @var $uniSender UniSenderWrapper
         */
        $uniSender = Yii::$app->get($this->uniSenderComponent);
        $response = $uniSender->sendQuery('sendEmail', $requestBody);

        var_dump($response);
        return !isset($response['error']);

    }
}