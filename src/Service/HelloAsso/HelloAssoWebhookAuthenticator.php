<?php
namespace App\Service\HelloAsso;
use Symfony\Component\HttpFoundation\Request;
final readonly class HelloAssoWebhookAuthenticator{public function __construct(private string $secret,private string $allowedIps,private bool $allowUnsigned,private string $environment){}public function isAuthenticated(Request $request,string $raw):bool{$ips=array_filter(array_map('trim',explode(',',$this->allowedIps)));if($ips!==[]&&!in_array($request->getClientIp(),$ips,true))return false;if($this->secret!==''){$sig=(string)$request->headers->get('x-ha-signature','');if($sig==='')return false;$calc=hash_hmac('sha256',$raw,$this->secret);return hash_equals(strtolower($calc),strtolower($sig));}return $this->allowUnsigned&&$this->environment!=='prod';}}
