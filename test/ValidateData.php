<?php
/**
 * Tabela de casos das validacoes de data e de codigo de tamanho fixo.
 *
 * Uso: php test/ValidateData.php
 *
 * Carrega os arquivos de src direto, sem autoload: o repositorio nao traz vendor, e
 * assim o teste roda num clone recem-feito.
 *
 * Os casos marcados "Nexus" sao os que apareceram num parecer de seguranca de 04/10/2026:
 * obra comecando no ano 1000 e terminando em 2322, banco com CNPJ "hein?" e ISPB "efg".
 * Eram todos aceitos porque o Validate::date() confere formato, nao faixa.
 */

require __DIR__ . '/../src/Convert.php';
require __DIR__ . '/../src/Validate.php';

use Idsy\Tools\Validate;

$falhas = 0;

function checa(string $titulo, bool $obtido, bool $esperado): void
{
    global $falhas;

    $ok = ($obtido === $esperado);

    if ($ok == false) {
        $falhas++;
    }

    printf("  %-52s %-6s %s\n", $titulo, $obtido ? 'true' : 'false',
           $ok ? '' : '<<< esperava ' . ($esperado ? 'true' : 'false'));
}

$anoQueVem  = (int)date('Y') + 1;
$muitoLonge = (int)date('Y') + 20;

echo "dateRange -- data valida E dentro da faixa (1900 ate hoje+15)\n";
checa('hoje em Y-m-d',                       Validate::dateRange(date('Y-m-d')), true);
checa('30/09/2026 em d/m/Y',                 Validate::dateRange('30/09/2026'), true);
checa('30-09-2026 em d-m-Y',                 Validate::dateRange('30-09-2026'), true);
checa('2026-09-30 10:20:30',                 Validate::dateRange('2026-09-30 10:20:30'), true);
checa('objeto DateTime',                     Validate::dateRange(new DateTime()), true);
checa('1000-01-01  (Nexus)',                 Validate::dateRange('1000-01-01'), false);
checa('2322-12-31  (Nexus)',                 Validate::dateRange('2322-12-31'), false);
checa('1899-12-31  (um ano antes do piso)',  Validate::dateRange('1899-12-31'), false);
checa('1900-01-01  (o piso)',                Validate::dateRange('1900-01-01'), true);
checa($anoQueVem . '-01-01',                 Validate::dateRange($anoQueVem . '-01-01'), true);
checa($muitoLonge . '-01-01',                Validate::dateRange($muitoLonge . '-01-01'), false);
checa("texto 'hein?'",                       Validate::dateRange('hein?'), false);
checa('vazio',                               Validate::dateRange(''), false);
checa('nulo',                                Validate::dateRange(null), false);
checa('31/02/2026 (dia que nao existe)',     Validate::dateRange('31/02/2026'), false);
checa('faixa propria: 2020 a +1 ano',        Validate::dateRange('2019-05-05', 2020, 1), false);

echo "\ndateOrder -- a segunda data nao vem antes da primeira\n";
checa('2026-01-01 -> 2026-12-31',            Validate::dateOrder('2026-01-01', '2026-12-31'), true);
checa('2026-12-31 -> 2026-01-01',            Validate::dateOrder('2026-12-31', '2026-01-01'), false);
checa('mesma data nas duas',                 Validate::dateOrder('2026-05-05', '2026-05-05'), true);
checa('formatos diferentes, ordem certa',    Validate::dateOrder('01/01/2026', '2026-12-31'), true);
checa('formatos diferentes, ordem errada',   Validate::dateOrder('31/12/2026', '2026-01-01'), false);
checa('segunda em branco',                   Validate::dateOrder('2026-01-01', ''), true);
checa('primeira em branco',                  Validate::dateOrder('', '2026-01-01'), true);
checa('as duas em branco',                   Validate::dateOrder(null, null), true);
checa('primeira ilegivel',                   Validate::dateOrder('hein?', '2026-01-01'), false);

echo "\ndigits -- codigo de tamanho exato, so digitos\n";
checa("ISPB '00000000' (8)",                 Validate::digits('00000000', 8), true);
checa("ISPB '60701190' (8)",                 Validate::digits('60701190', 8), true);
checa("ISPB 'efg' (8)   (Nexus)",            Validate::digits('efg', 8), false);
checa("ISPB '1234567' (8, falta um)",        Validate::digits('1234567', 8), false);
checa("ISPB '123456789' (8, sobra um)",      Validate::digits('123456789', 8), false);
checa("COMPE '237' (3)",                     Validate::digits('237', 3), true);
checa("COMPE '23' (3)",                      Validate::digits('23', 3), false);
checa("espaco nas pontas ' 237 '",           Validate::digits(' 237 ', 3), true);
checa("com ponto '23.7'",                    Validate::digits('23.7', 3), false);
checa('vazio',                               Validate::digits('', 3), false);
checa('quantidade zero',                     Validate::digits('237', 0), false);

echo "\no que ja existia continua como estava\n";
checa('cpf valido',                          Validate::cpf('529.982.247-25'), true);
checa('cpf invalido',                        Validate::cpf('111.111.111-11'), false);
checa("cnpj 'hein?'  (Nexus)",               Validate::cnpj('hein?'), false);
// documento() so conhece CPF, CNPJ, RG e PASSAPORTE: tipo fora disso cai no else e
// devolve false. Quem chama precisa saber, senao recusa CREA, CAU, CNH, PIS, IE, IM...
checa('documento CREA (tipo desconhecido)',  Validate::documento('123456', 'CREA'), false);
checa("date('1000-01-01') segue true",       Validate::date('1000-01-01'), true);

printf("\n%d caso(s) fora do esperado.\n", $falhas);

exit($falhas === 0 ? 0 : 1);
