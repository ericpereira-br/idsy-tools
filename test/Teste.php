<?php

require '../vendor/autoload.php';

use Idsy\Tools\Convert;

$json = '{"id":"32","nome":"33","producao_inicio":"2025-10-03","producao_fim":"2025-10-10","categoria":"Tela","descricao_material":"","descricao_poetica":"","img1":"http://localhost:8080/idsy-app/public_html/view/registroartistico/public_html/view/obra/set/undefined","img2":"http://localhost:8080/idsy-app/public_html/view/registroartistico/public_html/view/obra/set/undefined","img3":"http://localhost:8080/idsy-app/public_html/view/registroartistico/public_html/view/obra/set/undefined","blocked":"F","deleted":"F"}';

$json = 
'{"loc":{"id":207700920,"location":"https://spi-qrcode.bancointer.com.br/spi/pj/v2/daba2049fa4b4de5b31aad3b3deebde5","criacao":"2026-03-22T13:01:08.428Z","tipoCob":"cob"},"location":"https://spi-qrcode.bancointer.com.br/spi/pj/v2/daba2049fa4b4de5b31aad3b3deebde5","valor":{"original":"5.00","modalidadeAlteracao":1},"calendario":{"expiracao":3600,"criacao":"2026-03-22T13:01:08.517Z"},"txid":"TEST00000000000000000000000000004","revisao":0,"status":"ATIVA","pixCopiaECola":"00020101021226930014BR.GOV.BCB.PIX2571spi-qrcode.bancointer.com.br/spi/pj/v2/daba2049fa4b4de5b31aad3b3deebde552040000530398654045.005802BR5901*6007ITAPIRA61081397715562070503***6304C20B","pix":[],"chave":"41044508000179","solicitacaoPagador":"Registro Artistico","infoAdicionais":[{"nome":"Obra","valor":"Registro51"},{"nome":"Colecionador","valor":"ERIC PEREIRA"}]}';
$tese = Convert::jsonToChave($json, 'endToEndId');
echo Convert::jsonToChave($json, 'endToEndId');