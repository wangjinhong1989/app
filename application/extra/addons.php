<?php

return array (
  'autoload' => false,
  'hooks' => 
  array (
    'config_init' => 
    array (
      0 => 'nkeditor',
    ),
    'sms_send' => 
    array (
      0 => 'smsbao',
    ),
    'sms_notice' => 
    array (
      0 => 'smsbao',
    ),
    'sms_check' => 
    array (
      0 => 'smsbao',
    ),
  ),
  'route' => 
  array (
    '/third$' => 'third/index/index',
    '/third/connect/[:platform]' => 'third/index/connect',
    '/third/callback/[:platform]' => 'third/index/callback',
  ),
);