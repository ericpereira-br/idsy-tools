<?php

namespace Idsy\Tools;

use DateTime;

class Validate
{
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

    /**
     * Validate an email address.
     *
     * @param string $value Email address.
     * @return bool True if the email address is valid, false otherwise.
     */
    static function email(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    static function url(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    static function date(mixed $date): bool
    {
        if ($date instanceof \DateTime) {
            return true;
        }

        if (empty($date) || !is_string($date)) {
            return false;
        }

        $formatos = [
            'd/m/Y',
            'Y-m-d',
            'd-m-Y',
            'Y-m-d H:i:s'
        ];

        foreach ($formatos as $formato) {
            $d = \DateTime::createFromFormat($formato, $date);

            if ($d && $d->format($formato) === $date) {
                return true;
            }
        }

        return false;
    }

    /**
     * Formatos de data que esta classe entende. Mesma lista do date(), num lugar so.
     */
    private const FORMATOS_DATA = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'Y-m-d H:i:s'];

    /**
     * Devolve a data como objeto, ou null quando nao da para ler.
     *
     * Existe para o dateRange e o dateOrder nao precisarem repetir o laco de formatos, e
     * para nao depender do Convert::dateToMysql, que aceita menos formatos que o date()
     * desta classe -- usar ele faria 'd-m-Y' valer no date() e nao valer no dateRange.
     */
    private static function dateObject(mixed $date): ?\DateTime
    {
        if ($date instanceof \DateTime) {
            return $date;
        }

        if (empty($date) || !is_string($date)) {
            return null;
        }

        foreach (Validate::FORMATOS_DATA as $formato) {
            $result = \DateTime::createFromFormat($formato, $date);

            if ($result && $result->format($formato) === $date) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Data valida E dentro de uma faixa que faz sentido.
     *
     * O date() acima confere o FORMATO: '1000-01-01' passa por ele, porque e um Y-m-d
     * legitimo. Era por isso que um cadastro aceitava obra comecando no ano 1000 -- o
     * formato estava certo, a data e que nao existia no mundo.
     *
     * A faixa vai de $anoMinimo ate $anosAFrente anos a frente de hoje. Quinze anos a
     * frente porque passaporte e CNH valem dez, e previsao de termino de obra estica.
     *
     * Vazio devolve false, igual ao date(): campo opcional em branco e assunto de
     * obrigatoriedade, e quem chama e que sabe se aquele campo e obrigatorio.
     *
     * @param mixed $date Data em d/m/Y, Y-m-d, d-m-Y, Y-m-d H:i:s ou DateTime.
     * @param int $anoMinimo Primeiro ano aceito.
     * @param int $anosAFrente Quantos anos a frente de hoje ainda sao aceitos.
     */
    static function dateRange(mixed $date, int $anoMinimo = 1900, int $anosAFrente = 15): bool
    {
        $result = Validate::dateObject($date);

        if ($result === null) {
            return false;
        }

        $ano = (int)$result->format('Y');

        return ($ano >= $anoMinimo) && ($ano <= ((int)date('Y') + $anosAFrente));
    }

    /**
     * A segunda data nao vem antes da primeira.
     *
     * Para par que tem ordem obrigatoria: inicio e fim de obra, data do pedido e entrega
     * prevista, emissao e validade de documento.
     *
     * Falta uma das duas devolve TRUE: nao ha ordem para conferir com uma ponta so, e
     * recusar ali tiraria do cadastro o direito de preencher apenas o que ja se sabe.
     * Data ilegivel devolve false -- isso e problema de formato, nao de ordem.
     */
    static function dateOrder(mixed $de, mixed $ate): bool
    {
        if ((empty($de) == true) || (empty($ate) == true)) {
            return true;
        }

        $inicio = Validate::dateObject($de);
        $fim = Validate::dateObject($ate);

        if (($inicio === null) || ($fim === null)) {
            return false;
        }

        return $fim >= $inicio;
    }

    static function cpf(string $document): bool
    {
        // Remove caracteres não numéricos
        $document = Convert::onlyDigits($document);

        // Verifica se tem 11 dígitos
        if (strlen($document) != 11) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(.)\1{10}$/', $document)) {
            return false;
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
            return true;
        } else {
            return false;
        }
    }

    static function cnpj(string $document): bool
    {
        // Remove caracteres não numéricos
        $document = Convert::onlyDigits($document);

        // Verifica se tem 14 dígitos
        if (strlen($document) != 14) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(.)\1{13}$/', $document)) {
            return false;
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
            return true;
        } else {
            return false;
        }
    }

    static function rg(string $document): bool
    {
        // Remove caracteres não numéricos
        $document = Convert::onlyDigits($document);

        // Verifica se tem entre 5 e 14 dígitos
        $length = strlen($document);
        if ($length < 5 || $length > 14) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(.)\1{' . ($length - 1) . '}$/', $document)) {
            return false;
        }

        return true;
    }

    static function telefone(string $value): bool
    {
        // Remove caracteres não numéricos
        $document = Convert::onlyDigits($value);

        // Verifica se tem entre 10 e 11 dígitos
        $length = strlen($value);
        if ($length < 10 || $length > 11) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(.)\1{' . ($length - 1) . '}$/', $value)) {
            return false;
        }

        return true;
    }

    static function cep(string $value): bool
    {
        // Remove caracteres não numéricos
        $value = Convert::onlyDigits($value);

        // Verifica se tem 8 dígitos
        if (strlen($value) != 8) {
            return false;
        }

        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(.)\1{7}$/', $value)) {
            return false;
        }

        return true;
    }

    static function passaportBR(string $document): bool
    {
        // Remove espaços em branco
        $document = trim($document);

        // Verifica se tem entre 5 e 20 caracteres alfanuméricos
        $length = strlen($document);
        if ($length < 5 || $length > 20) {
            return false;
        }

        // Verifica se contém apenas caracteres alfanuméricos
        if (!preg_match('/^[a-zA-Z0-9]+$/', $document)) {
            return false;
        }

        return true;
    }

    static function passaportInternacional(string $document): bool
    {
        // Regex: 5 a 9 caracteres, letras e números apenas
        if (preg_match('/^[A-Z0-9]{5,9}$/', $document)) {
            return false;
        }

        return true;
    }

    static function passaport(string $document, string $type): bool
    {
        $result = Validate::passaportBR($document, $type);

        if ($result == false) {
            $result = Validate::passaportInternacional($document, $type);
        }

        return $result;
    }

    static function documento(string $documento, string $type): bool
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
            return false;
        }
    }

    /**
     * Numero de tamanho exato, so digitos.
     *
     * Para campo que e codigo e nao conta: ISPB tem 8 digitos e o codigo COMPE de banco
     * tem 3. Sem isto, um banco entrava no cadastro com ISPB "efg".
     *
     * @param string $value Valor como foi digitado.
     * @param int $quantidade Quantos digitos, exatamente.
     */
    static function digits(string $value, int $quantidade): bool
    {
        if ($quantidade <= 0) {
            return false;
        }

        return preg_match('/^[0-9]{' . $quantidade . '}$/', trim($value)) === 1;
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
        if ((empty($_SERVER['REMOTE_ADDR']) == false) && (isset($_SERVER['REMOTE_ADDR']) == true)) {
            return $_SERVER['REMOTE_ADDR'];
        } else {
            throw new \Exception('IP do cliente não encontrado!');
        }
    }
}
