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
}
