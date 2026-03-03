<?php
namespace Idsy\Tools;

use Idsy\Tools\Generator;

class Validate {
    static function base64(string $img): bool
    {
        if (isset($img) == false) {
            return false;
        }

        if (empty($img) == true) {
            return false;
        }

        if (preg_match('/^data:image\/(\w+);base64,/', $img, $m)) {
            return true;
        } else {
            return false;
        }
    }

/*************  ✨ Windsurf Command ⭐  *************/
    /**
     * Validate an email address.
     *
     * @param string $value Email address.
     * @return bool True if the email address is valid, false otherwise.
     */
/*******  ea1aec7d-4ce0-4179-9687-670f5464bb89  *******/
    static function email(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    static function url(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    static function date(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    static function cpf(string $document): array
    {
        // Remove caracteres não numéricos
        $document = Convert::onlyDigits($document);

        // Verifica se tem 11 dígitos
        if (strlen($document) != 11) {
            return [false, ''];
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(.)\1{10}$/', $document)) {
            return [false, ''];
        }

        // Calcula o primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int)$document[$i] * (10 - $i);
        }
        $rest = $sum % 11;
        $digit1 = ($rest < 2) ? 0 : 11 - $rest;

        // Calcula o segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int)$document[$i] * (11 - $i);
        }
        $rest = $sum % 11;
        $digit2 = ($rest < 2) ? 0 : 11 - $rest;

        // Verifica se os dígitos calculados conferem com os dígitos informados
        if ($digit1 == (int)$document[9] && $digit2 == (int)$document[10]) {
            return [
                "valido" => true,
                "documento" => $document
            ];
        } else {
            return [
                "valido" => false,
                "documento" => $document
            ];
        }
    }

    static function cnpj(string $document): array
    {
        // Remove caracteres não numéricos
        $document = Convert::onlyDigits($document);

        // Verifica se tem 14 dígitos
        if (strlen($document) != 14) {
            return [
                "valido" => false,
                "documento" => $document
            ];
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(.)\1{13}$/', $document)) {
            return [
                "valido" => false,
                "documento" => $document
            ];
        }

        // Calcula o primeiro dígito verificador
        $sum = 0;
        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$document[$i] * $weights[$i];
        }
        $rest = $sum % 11;
        $digit1 = ($rest < 2) ? 0 : 11 - $rest;

        // Calcula o segundo dígito verificador
        $sum = 0;
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 13; $i++) {
            $sum += (int)$document[$i] * $weights[$i];
        }
        $rest = $sum % 11;
        $digit2 = ($rest < 2) ? 0 : 11 - $rest;

        // Verifica se os dígitos calculados conferem com os dígitos informados
        if ($digit1 == (int)$document[12] && $digit2 == (int)$document[13]) {
            return [
                "valido" => true,
                "documento" => $document
            ];
        } else {
            return [
                "valido" => false,
                "documento" => $document
            ];
        }
    }

    static function rg(string $document): array
    {
        // Remove caracteres não numéricos
        $document = Convert::onlyDigits($document);

        // Verifica se tem entre 5 e 14 dígitos
        $length = strlen($document);
        if ($length < 5 || $length > 14) {
            return [
                "valido" => false,
                "documento" => $document
            ];
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(.)\1{' . ($length - 1) . '}$/', $document)) {
            return [
                "valido" => false,
                "documento" => $document
            ];
        }

        return [
            "valido" => true,
            "documento" => $document
        ];
    }

    static function telefone(string $value): array
    {
        // Remove caracteres não numéricos
        $document = Convert::onlyDigits($value);

        // Verifica se tem entre 10 e 11 dígitos
        $length = strlen($value);
        if ($length < 10 || $length > 11) {
            return [
                "valido" => false,
                "documento" => $value
            ];
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(.)\1{' . ($length - 1) . '}$/', $value)) {
            return [
                "valido" => false,
                "value" => $value
            ];
        }

        return [
            "valido" => true,
            "value" => $value
        ];
    }

    static function cep(string $value): array
    {
        // Remove caracteres não numéricos
        $value = Convert::onlyDigits($value);

        // Verifica se tem 8 dígitos
        if (strlen($value) != 8) {
            return [
                "valido" => false,
                "value" => $value
            ];
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(.)\1{7}$/', $value)) {
            return [
                "valido" => false,
                "value" => $value
            ];
        }

        return [
            "valido" => true,
            "value" => $value
        ];
    }

    static function passaportBR(string $document): array
    {
        // Remove espaços em branco
        $document = trim($document);

        // Verifica se tem entre 5 e 20 caracteres alfanuméricos
        $length = strlen($document);
        if ($length < 5 || $length > 20) {
            return [
                "valido" => false,
                "documento" => $document
            ];
        }

        // Verifica se contém apenas caracteres alfanuméricos
        if (!preg_match('/^[a-zA-Z0-9]+$/', $document)) {
            return [
                "valido" => false,
                "documento" => $document
            ];
        }

        return [
            "valido" => true,
            "documento" => $document
        ];
    }

    static function passaportInternacional(string $document): array
    {
        // Regex: 5 a 9 caracteres, letras e números apenas
        if (preg_match('/^[A-Z0-9]{5,9}$/', $document)) {
            return [
                "valido" => true,
                "documento" => $document
            ];
        }

        return [
            "valido" => true,
            "documento" => $document
        ];
    }

    static function passaport(string $document, string $type): array
    {
        $result = Validate::passaportBR($document, $type);

        if ($result['valida'] == false) {
            $result = Validate::passaportInternacional($document, $type);
        }

        return $result;
    }

    static function documento(string $documento, string $type): array
    {
        if ($type == 'CPF') {
            return Validate::cpf($documento);
        } else if (strtoupper($type) == 'CNPJ') {
            return Validate::cnpj($documento);
        } else if (strtoupper($type) == 'RG') {
            return Validate::rg($documento);
        } else if (strtoupper($type) == 'PASSAPORTE') {
            return Validate::passaport($documento, $type);
        } else {
            return [false, ''];
        }
    }

    public static function pix($criacao, $expiracao): bool
    {
        $criacaoTs = strtotime($criacao);
        $expiraEm  = $criacaoTs + $expiracao;
        $expirou = time() > $expiraEm;
        return !$expirou;
    }   
    
    public static function json(string $string): bool
    {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }     

        public static function getIp()
        {
            if ((empty($_SERVER['REMOTE_ADDR']) == false) && (isset($_SERVER['REMOTE_ADDR']) == true))
            {
                return $_SERVER['REMOTE_ADDR'];
            }
            else
            {
                throw new \Exception('IP do cliente não encontrado!');        
            }   
        }      
}