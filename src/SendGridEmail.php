<?php

namespace Hdruk\LaravelMjml;

use SendGrid;
use SendGrid\Mail\Mail as SGMail;
use SendGrid\Response;

class SendGridEmail
{
    protected SendGrid $client;
    protected string $apiKey;
    
    // Email properties
    protected ?string $fromEmail = null;
    protected ?string $fromName = null;
    protected ?string $toEmail = null;
    protected ?string $toName = null;
    protected ?string $subject = null;
    protected ?string $htmlContent = null;
    protected ?string $textContent = null;
    protected array $customArgs = [];
    protected ?string $jobUuid = null;
    
    // Response properties
    protected ?Response $response = null;
    protected array $headers = [];
    protected ?int $statusCode = null;
    protected ?string $body = null;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? config('speedi.system.sendgrid_api_key');
        $this->client = new SendGrid($this->apiKey);
        
        // Set defaults from config
        $this->fromEmail = config('mail.from.address');
        $this->fromName = config('mail.from.name');
    }

    /**
     * Set to email
     */
    public function setToEmail(string $email): self
    {
        $this->toEmail = $email;
        return $this;
    }

    /**
     * Get to email
     */
    public function getToEmail(): ?string
    {
        return $this->toEmail;
    }

    /**
     * Set to name
     */
    public function setToName(?string $name): self
    {
        $this->toName = $name;
        return $this;
    }

    /**
     * Get to name
     */
    public function getToName(): ?string
    {
        return $this->toName;
    }

    /**
     * Set subject
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Get subject
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * Set HTML content
     */
    public function setHtmlContent(string $html): self
    {
        $this->htmlContent = $html;
        return $this;
    }

    /**
     * Get HTML content
     */
    public function getHtmlContent(): ?string
    {
        return $this->htmlContent;
    }

    /**
     * Set job UUID
     */
    public function setJobUuid(?string $uuid): self
    {
        $this->jobUuid = $uuid;
        return $this;
    }

    /**
     * Get job UUID
     */
    public function getJobUuid(): ?string
    {
        return $this->jobUuid;
    }

    /**
     * Send email using current properties
     */
    public function send(?array $params = null): self
    {
        // If params provided, merge with current properties
        if ($params) {
            $this->mergeParams($params);
        }

        // Create email
        $email = $this->buildEmail();

        // Send email
        $this->response = $this->client->send($email);
        
        // Store response data
        $this->statusCode = $this->response->statusCode();
        $this->body = $this->response->body();
        $this->headers = $this->response->headers();

        return $this;
    }

    /**
     * Merge params with current properties
     */
    protected function mergeParams(array $params): void
    {
        if (isset($params['to_email'])) {
            $this->toEmail = $params['to_email'];
        }
        if (isset($params['to_name'])) {
            $this->toName = $params['to_name'];
        }
        if (isset($params['subject'])) {
            $this->subject = $params['subject'];
        }
        if (isset($params['html_content'])) {
            $this->htmlContent = $params['html_content'];
        }
        if (isset($params['job_uuid'])) {
            $this->jobUuid = $params['job_uuid'];
        }
    }

    /**
     * Build the SendGrid Mail object
     */
    protected function buildEmail(): SGMail
    {
        $email = new SGMail();

        // Set sender
        $email->setFrom($this->fromEmail, $this->fromName);

        // Set recipient
        $email->addTo($this->toEmail, $this->toName);

        // Set subject
        $email->setSubject($this->subject);

        // Set content
        if ($this->htmlContent) {
            $email->addContent("text/html", $this->htmlContent);
        }
        if ($this->textContent) {
            $email->addContent("text/plain", $this->textContent);
        }

        // Add job UUID if set
        if ($this->jobUuid) {
            $email->addCustomArg('job_uuid', $this->jobUuid);
        }

        return $email;
    }

    /**
     * Get HTTP status code
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * Get response body
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * Get all headers
     */
    public function getAllHeaders(): array
    {
        return $this->headers;
    }

}