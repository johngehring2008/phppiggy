<?php

declare(strict_types=1);

use Framework\Http;

function dd(mixed $data)
{
  echo "<pre>";
  var_dump($data);
  echo "</pre>";
  die();
}

function e(mixed $value): string
{
  return htmlspecialchars((string) $value);
}

function redirectTo(string $path)
{
  header("Location: {$path}");
  http_response_code(HTTP::REDIRECT_STATUS_CODE);
  exit;
}
