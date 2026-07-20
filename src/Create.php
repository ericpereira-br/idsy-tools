<?php

namespace Idsy\Tools;

use Endroid\QrCode\
{
    Color\Color,
    Encoding\Encoding,
    ErrorCorrectionLevel,
    QrCode,
    Label\Label,
    Logo\Logo,
    RoundBlockSizeMode,
    Writer\PngWriter
};

class Create
{
    public static function sql(string $manager, $fields, $from, $where, $orderBy, $lines): string
    {
        $sql = '';
        $resultLines = '';

        if ($where != '') {
            $where = ' where ' . $where;
        }

        if ($manager == 'mysql') {
            if (($lines <> '') and ($lines <> '0')) {
                $resultLines = ' limit ' . $lines;
            }

            $sql =
                ' select ' . $fields .
                ' from ' . $from .
                $where .
                $orderBy .
                $resultLines;
        } else
            if ($manager == 'firebird') {
            if (($lines <> '') and ($lines <> '0')) {
                $resultLines = ' first ' . $lines;
            }

            $sql =
                ' select ' . $resultLines . $fields .
                ' from ' . $from .
                $where .
                $orderBy;
        } else
            if ($manager == 'sqlserver') {
            if (($lines <> '') and ($lines <> '0')) {
                $resultLines = ' top ' . $lines;
            }

            $sql =
                ' select ' . $resultLines . $fields .
                ' from ' . $from .
                $where .
                $orderBy;
        } else
            if ($manager == 'oracle') {
            if (($lines <> '') and ($lines <> '0')) {
                $resultLines = ' rowcount ' . $lines;
            }

            $sql =
                ' select ' . $fields .
                ' from ' . $from .
                $where .
                $orderBy .
                $resultLines;
        } else {
            throw new \Exception('Tools::createSQL(Banco de dados não suportado)');
        }
        return $sql;
    }

    /**
     * Gerar o txid.     * 
     *
     * @param string $value
     * @return string
     */
    public static function txid(string $sigla, int $sequencia): string
    {
        $tamanho = 33;
        $tamanho = $tamanho - strlen($sigla);
        return $sigla . str_pad($sequencia, $tamanho, '0', STR_PAD_LEFT);
    }

    public static function log($path, $name, $message)
    {
        $fp = fopen($path . $name . '.log', "a+");
        fwrite($fp, date('d/m/Y H:i:s'));
        fwrite($fp, "\n");        
        fwrite($fp, $message);
        fwrite($fp, "\n");
        fwrite($fp, '-----------------------------------');
        fwrite($fp, "\n");
        fclose($fp);
    }

    public static function qrCode(string $url, string $img, string $labelText): string
    {
        $writer = new PngWriter();

        // Create QR code
        $qrCode = new QrCode(
            data: $url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );

        // Create generic logo
        $logo = new Logo(
            path: $img,
            resizeToWidth: 60,
            punchoutBackground: true
        );

        // Create generic label
        $label = new Label(
            text: $labelText,
            textColor: new Color(255, 0, 0)
        );
        $result = $writer->write($qrCode, $logo, $label);
        $base64 = "data:image/jpeg;base64," . base64_encode($result->getString());
        return $base64;
    }

    public static function saveBase64Jpeg(string $base64, string $arquivo, int $qualidade = 90): bool
    {
        // A conversão depende da extensão GD
        if (!extension_loaded('gd')) {
            return false;
        }

        // Remove o cabeçalho data URI, se existir (ex.: "data:image/png;base64,...")
        if (str_contains($base64, ',')) {
            $base64 = explode(',', $base64, 2)[1];
        }

        $dados = base64_decode($base64, true);
        if ($dados === false) {
            return false;
        }

        // Entrada externa: suprime o warning e trata o retorno
        $imagem = @imagecreatefromstring($dados);
        if ($imagem === false) {
            return false;
        }

        // Cria diretório, tratando falha e concorrência
        $diretorio = dirname($arquivo);
        if (!is_dir($diretorio) && !mkdir($diretorio, 0755, true) && !is_dir($diretorio)) {
            imagedestroy($imagem);
            return false;
        }

        // Fundo branco para achatar transparência (PNG/WebP) — evita áreas pretas no JPEG
        $jpeg = imagecreatetruecolor(imagesx($imagem), imagesy($imagem));
        if ($jpeg === false) {
            imagedestroy($imagem);
            return false;
        }

        imagealphablending($jpeg, true); // mescla pixels alfa sobre o branco
        $branco = imagecolorallocate($jpeg, 255, 255, 255);
        imagefill($jpeg, 0, 0, $branco);
        imagecopy($jpeg, $imagem, 0, 0, 0, 0, imagesx($imagem), imagesy($imagem));

        $qualidade = max(0, min(100, $qualidade));
        $resultado = imagejpeg($jpeg, $arquivo, $qualidade);

        imagedestroy($jpeg);
        imagedestroy($imagem);

        // imagejpeg pode retornar true sem o arquivo existir de fato
        return $resultado && is_file($arquivo);
    }    
}
