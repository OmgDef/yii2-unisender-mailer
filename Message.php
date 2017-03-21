<?php

namespace omgdef\yii\unisender\mailer;


use yii\base\NotSupportedException;
use yii\mail\BaseMessage;

class Message extends BaseMessage
{
    protected $htmlBody;
    protected $textBody;
    protected $charset = 'UTF-8';
    protected $from;
    protected $replyTo;
    protected $to;
    protected $cc;
    protected $bcc;
    protected $subject;
    protected $attachments = [];

    /**
     * @inheritdoc
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @inheritdoc
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @inheritdoc
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * @inheritdoc
     */
    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @inheritdoc
     */
    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * @inheritdoc
     */
    public function setCc($cc)
    {
        $this->cc = $cc;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * @inheritdoc
     */
    public function setBcc($bcc)
    {
        $this->bcc = $bcc;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @inheritdoc
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTextBody($text)
    {
        $this->textBody = $text;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setHtmlBody($html)
    {
        $this->htmlBody = $html;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->htmlBody ?: $this->textBody;
    }

    /**
     * @inheritdoc
     */
    public function attach($fileName, array $options = [])
    {
        if (is_file($fileName)) {
            $content = file_get_contents($fileName);

            if (!empty($options['fileName'])) {
                $name = $options['fileName'];
            } else {
                $name = basename($fileName);
            }

            $this->addAttachment($name, $content);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function attachContent($content, array $options = [])
    {
        if (!empty($options['fileName'])) {
            $name = $options['fileName'];
        } else {
            $name = 'Attachment.txt';
        }
        $this->addAttachment($name, $content);

        return $this;
    }

    /**
     * @param $name
     * @param $content
     */
    protected function addAttachment($name, $content)
    {
        $idx = 1;
        $newName = $name;
        while (array_key_exists($newName, $this->attachments)) {
            $newName = $name;
            $newName = "($idx)" . $newName;
            $idx++;
        }
        $this->attachments[$newName] = $content;
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @inheritdoc
     */
    public function embed($fileName, array $options = [])
    {
        throw new NotSupportedException('embed can not be implemented via Unisender');
    }

    /**
     * @inheritdoc
     */
    public function embedContent($content, array $options = [])
    {
        throw new NotSupportedException('embed can not be implemented via Unisender');
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        $attributes = [
            'htmlBody',
            'textBody',
            'charset',
            'from',
            'replyTo',
            'to',
            'cc',
            'bcc',
            'subject',
        ];

        $data = [];
        foreach ($attributes as $attribute) {
            $data[$attribute] = $this->$attribute;
        }
        return print_r($data, true);
    }
}