<?php
namespace App\Event\Mail;

abstract class MailEvent
{
    private string $email;
    private string $template;
    private string $subject;
    private array  $params;
    private string $replyTo;

    public function __construct(
        string $email,
    ) {
        $this->email = $email;
        $this->replyTo = '';
        $this->template = '';
        $this->subject = '';
        $this->params = [];
    }


    /**
     * @param string $replyTo
     */
    public function setReplyTo(string $replyTo): MailEvent
    {
        $this->replyTo = $replyTo;
        return $this;
    }

    /**
     * @return string
     */
    public function getReplyTo(): string
    {
        return $this->replyTo;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

}