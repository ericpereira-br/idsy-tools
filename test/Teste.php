<?php

require '../vendor/autoload.php';

use Idsy\Tools\Convert;

$json = '{"id":"32","nome":"33","producao_inicio":"2025-10-03","producao_fim":"2025-10-10","categoria":"Tela","descricao_material":"","descricao_poetica":"","img1":"http://localhost:8080/idsy-app/public_html/view/registroartistico/public_html/view/obra/set/undefined","img2":"http://localhost:8080/idsy-app/public_html/view/registroartistico/public_html/view/obra/set/undefined","img3":"http://localhost:8080/idsy-app/public_html/view/registroartistico/public_html/view/obra/set/undefined","blocked":"F","deleted":"F"}';

$json = 
'{
  "usuario": {
    "id": 1,
    "nome": "Eric",
    "contato": {
      "email": "eric@email.com",
      "telefone": "11999999999"
    },
    "endereco": {
      "cidade": "Campinas",
      "estado": "SP",
      "geo": {
        "lat": -22.9056,
        "lng": -47.0608
      }
    }
  },
  "transacoes": [
    {
      "id": "tx1",
      "valor": {
        "original": "100.00",
        "moeda": "BRL"
      }
    },
    {
      "id": "tx2",
      "valor": {
        "original": "250.50",
        "moeda": "BRL"
      }
    }
  ]
}';

echo Convert::jsonToChave($json, 'id');