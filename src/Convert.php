<?php

namespace Idsy\Tools;

class Convert
{
    public static function onlyNumber(string $value): null|int
    {
        if ($value == 'NULL' or ($value == 'null')) {
            return null;
        } else {
            $result = preg_replace('/[^0-9]/', '', $value);
            return intval($result);
        }
    }

    public static function onlyDigits(string $value): string
    {
        return preg_replace('/\D+/', '', $value);
    }

    public static function strToBool(string $value): string
    {
        if (strtoupper($value == 'TRUE') or (strtoupper($value == 'T'))) {
            return 'T';
        } else
            if (strtoupper($value == 'FALSE') or (strtoupper($value == 'F'))) {
            return 'F';
        } else {
            return 'F';
        }
    }

    public static function dataToType(string $value, $type)
    {
        if (strtoupper($type) == 'JSON') {
            return json_decode($value);
        } else
            if (strtoupper($type) == 'TEXT') {
            return $value;
        }
        if (strtoupper($type) == 'NUMBER') {
            return Convert::onlyNumber($value);
        }
    }

    static function strToFloat(string $valor): float
    {
        // Remove "R$", espaços e outros caracteres não numéricos, exceto vírgula e ponto
        $valor = preg_replace('/[^\d,.-]/', '', $valor);

        // Substitui vírgula por ponto, se for usada como separador decimal
        if (strpos($valor, ',') !== false && strpos($valor, '.') === false) {
            $valor = str_replace(',', '.', $valor);
        } elseif (strpos($valor, ',') !== false && strpos($valor, '.') !== false) {
            // Caso o valor seja do tipo "1.234,56"
            $valor = str_replace('.', '', $valor);      // remove separador de milhar
            $valor = str_replace(',', '.', $valor);     // troca decimal
        }

        return floatval($valor);
    }

    static function imageJsonToJpg(string $imgBase64, int $maxSize): string
    {
        if (isset($imgBase64) == false) {
            throw new \Exception('convertImageJsonToJpg(imgBase64 isset)');
        }

        if (empty($imgBase64) == true) {
            throw new \Exception('convertImageJsonToJpg(imgBase64 empty)');
        }

        if (isset($maxSize) == false) {
            throw new \Exception('convertImageJsonToJpg(maxSize isset)');
        }

        if ($maxSize <= 0) {
            throw new \Exception('convertImageJsonToJpg(maxSize invalid)');
        }

        // Remove prefixo "data:image/...;base64,"
        if (strpos($imgBase64, "base64,") !== false) {
            $imageData = explode("base64,", $imgBase64, 2)[1];
        }

        // Decodifica base64
        $decoded = base64_decode($imageData, true);
        if ($decoded === false) {
            throw new \Exception('convertImageJsonToJpg(Erro ao decodificar base64)');
        }

        // Cria imagem GD
        $srcImg = imagecreatefromstring($decoded);
        if (!$srcImg) {
            throw new \Exception('convertImageJsonToJpg(Erro ao criar imagem GD)');
        }

        // Dimensões originais
        $width  = imagesx($srcImg);
        $height = imagesy($srcImg);

        // Calcula proporção (limite de largura/altura)
        $ratio = min($maxSize / $width, $maxSize / $height, 1);
        $newWidth  = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        // Redimensiona
        $dstImg = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Salva em buffer como JPG
        ob_start();
        imagejpeg($dstImg, null, 85); // qualidade 85
        $jpgData = ob_get_clean();

        // Converte para base64
        $base64 = "data:image/jpeg;base64," . base64_encode($jpgData);

        // Libera memória
        imagedestroy($srcImg);
        imagedestroy($dstImg);
        return $base64;
    }

    static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);

        if (count($parts) !== 2) {
            return $email; // não é um e-mail válido
        }

        $name = $parts[0];
        $domain = $parts[1];

        $length = strlen($name);

        if ($length <= 2) {
            $masked = str_repeat('*', $length);
        } else {
            $start = substr($name, 0, 2);
            $end = substr($name, -2);
            $masked = $start . str_repeat('*', $length - 4) . $end;
        }

        return $masked . '@' . $domain;
    }
}
