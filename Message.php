<?php

namespace omgdef\yii\unisender\mailer;


use yii\base\Exception;
use yii\base\InvalidCallException;
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
    protected $embed = [];

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
     * @return string|null
     */
    public function getHtmlBody()
    {
        return $this->htmlBody;
    }

    /**
     * @return string|null
     */
    public function getTextBody()
    {
        return $this->textBody;
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

            $this->addAttachment($name, $content, mime_content_type($fileName));
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function attachContent($content, array $options = [])
    {
        if (empty($options['fileName'])) {
            throw new InvalidCallException('You have to specify "fileName"');
        }

        if (empty($options['contentType'])) {
            throw new InvalidCallException('You have to specify "contentType"');
        }

        $name = $options['fileName'];
        $type = $options['contentType'];

        $this->addAttachment($name, $content, $type);

        return $this;
    }

    /**
     * @param $name
     * @param $content
     * @param $type
     */
    protected function addAttachment($name, $content, $type)
    {
        $idx = 1;
        $newName = $name;

        while (true) {
            $found = false;
            foreach ($this->attachments as $attachment) {
                if ($attachment['name'] === $newName) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                $newName = "($idx)$name";
                $idx++;
            } else {
                break;
            }
        }

        $this->attachments[] = [
            'name' => $newName,
            'content' => $content,
            'type' => $type,
        ];
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @return array
     */
    public function getEmbed()
    {
        return $this->embed;
    }

    /**
     * @inheritdoc
     */
    public function embed($fileName, array $options = [])
    {
        if (is_file($fileName)) {
            return $this->addEmbed(file_get_contents($fileName), mime_content_type($fileName));
        }

        throw new Exception("File {$fileName} not found");
    }

    /**
     * @inheritdoc
     */
    public function embedContent($content, array $options = [])
    {
        if (empty($options['contentType'])) {
            throw new InvalidCallException('You have to specify "contentType"');
        }

        return $this->addEmbed($content, $options['contentType']);
    }

    /**
     * @param $content
     * @param $type
     * @return string
     */
    protected function addEmbed($content, $type)
    {
        $name = strtoupper(uniqid());
        $this->embed[] = [
            'name' => $name,
            'content' => $content,
            'type' => $type,
        ];

        return $name;
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