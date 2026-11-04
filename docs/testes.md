# Testes

## Roteiro de testes no terminal

Comando: `php teste.php`

```text
=== Teste 1: polimorfismo (mesmo método, respostas diferentes) ===
Pão de queijo [Unitário]: 2 x R$ 4,00 = R$ 8,00
Carne de sol [Por peso]: 2 x R$ 48,00 = R$ 96,00
Iogurte [Perecível]: 2 x R$ 10,00 = R$ 14,00
Queijo coalho [Perecível]: 2 x R$ 30,00 = R$ 60,00

=== Teste 2: sobrescrita de calcularPreco() no perecível ===
Iogurte (vence em 1 dia), 1 un: R$ 7,00 (esperado R$ 7,00)
Queijo (vence em 10 dias), 1 un: R$ 30,00 (esperado R$ 30,00)

=== Teste 3: composição (pedido com itens e total) ===
Pão de queijo x3 = R$ 12,00
Carne de sol x1.5 = R$ 72,00
Iogurte x2 = R$ 14,00
Total de Maria: R$ 98,00 (esperado R$ 98,00)

=== Teste 4: regras de Produto (nome, preço e quantidade) ===
OK, recusado: O nome do produto não pode ficar vazio.
OK, recusado: O preço do produto deve ser maior que zero.
OK, recusado: A quantidade deve ser maior que zero.

=== Teste 5: regras das filhas (unitário e por peso) ===
OK, recusado: Produto unitário só aceita quantidade inteira.
OK, recusado: A compra mínima de Carne de sol é 0,5 kg.
OK, recusado: O peso mínimo deve ser maior que zero.

=== Teste 6: regras do produto perecível (validade) ===
OK, recusado: A validade deve ser uma data válida no formato AAAA-MM-DD.
OK, recusado: O produto perecível não pode estar vencido.

=== Teste 7: regras do pedido (cliente e produto repetido) ===
OK, recusado: O nome do cliente não pode ficar vazio.
OK, recusado: O produto Pão de queijo já está no pedido.

=== Teste 8: paraArray() e JSON do pedido ===
{
    "cliente": "Maria",
    "itens": [
        {
            "produto": {
                "nome": "Pão de queijo",
                "tipo": "Unitário",
                "preco": 4
            },
            "quantidade": 3,
            "subtotal": 12
        },
        {
            "produto": {
                "nome": "Carne de sol",
                "tipo": "Por peso",
                "preco": 48,
                "peso_minimo": 0.5
            },
            "quantidade": 1.5,
            "subtotal": 72
        },
        {
            "produto": {
                "nome": "Iogurte",
                "tipo": "Perecível",
                "preco": 10,
                "validade": "2026-11-04",
                "em_promocao": true
            },
            "quantidade": 2,
            "subtotal": 14
        }
    ],
    "total": 98
}
```

**Explicação de cada linha:**

**Teste 1: polimorfismo (OO3).** O `foreach` percorre quatro objetos de classes diferentes (`ProdutoUnitario`, `ProdutoPorPeso` e duas `ProdutoPerecivel`) e chama `getTipo()` e `calcularPreco(2)` em todos, sem saber a classe de cada um. Cada linha mostra o tipo que o próprio objeto devolveu. O pão (2 x 4,00 = 8,00) e a carne (2 x 48,00 = 96,00) seguem o cálculo da classe mãe. O iogurte, que vence amanhã, sai a 14,00 em vez de 20,00 por causa do desconto de 30%. O queijo coalho, que vence em 10 dias, sai a 60,00, sem desconto.

**Teste 2: sobrescrita de `calcularPreco()` (OO2).** Confirma os valores do teste 1 para uma unidade: o iogurte custa R$ 7,00 (10,00 com 30% de desconto) e o queijo custa R$ 30,00 cheios. Comprova que só `ProdutoPerecivel` altera o cálculo, e só quando faltam 2 dias ou menos para vencer.

**Teste 3: composição (OO6).** Um `Pedido` recebe três `ItemPedido` por `adicionarItem`. Os subtotais são 3 x 4,00 = 12,00; 1,5 kg x 48,00 = 72,00; e 2 x 10,00 com desconto = 14,00. `calcularTotal()` soma tudo e dá R$ 98,00, igual ao valor esperado calculado à mão.

**Teste 4: regras de `Produto` (OO5).** Três recusas com `throw new Exception` tratadas por `try/catch`: nome vazio, preço igual a zero e quantidade igual a zero. Em cada uma o objeto não chega a ser criado e a mensagem da exceção é impressa.

**Teste 5: regras das filhas (OO5).** O produto unitário recusa quantidade fracionada (1,5). O produto por peso recusa 0,2 kg porque o mínimo da carne de sol é 0,5 kg, e recusa ser criado com peso mínimo zero. Mostra que cada filha acrescenta a sua regra por cima da regra da mãe.

**Teste 6: regras do perecível (OO5).** A data `amanhã` é recusada porque não está no formato AAAA-MM-DD, e uma data de ontem é recusada porque o produto estaria vencido.

**Teste 7: regras do pedido (OO5).** Um pedido sem nome de cliente é recusado, e adicionar o Pão de queijo pela segunda vez também, porque o produto já está no pedido.

**Teste 8: `paraArray()` e JSON (OO7).** Imprime o `paraArray()` do pedido com `json_encode`. É o mesmo formato que a rota `?formato=json` devolve. Mostra que cada classe contribui com os seus campos (`peso_minimo` na carne, `validade` e `em_promocao` no iogurte).

Nenhuma linha começa com `ERRO`: todas as recusas esperadas aconteceram. As datas de validade dependem do dia em que o teste roda.

## Camada web

Servidor: `php -S localhost:8000 -t public -d display_errors=1 -d output_buffering=0`

**Uma página que existe:** `curl.exe -i "http://localhost:8000/?pagina=produtos"` (só até o início do HTML)

```text
HTTP/1.1 200 OK
Host: localhost:8000
Date: Tue, 03 Nov 2026 22:11:27 GMT
Connection: close
X-Powered-By: PHP/8.3.6
Content-type: text/html; charset=UTF-8

<!DOCTYPE html>
<html lang="pt-BR">
```

Status `200 OK` e `Content-type: text/html; charset=UTF-8`: a página foi escolhida por `?pagina=produtos`.

**Uma página que não existe:** `curl.exe -i "http://localhost:8000/?pagina=inexistente"`

```text
HTTP/1.1 404 Not Found
Host: localhost:8000
Date: Tue, 03 Nov 2026 22:11:27 GMT
Connection: close
X-Powered-By: PHP/8.3.6
Content-type: text/html; charset=UTF-8

<!DOCTYPE html>
<html lang="pt-BR">
```

Status `404 Not Found`. O corpo continua sendo uma página HTML, com o mesmo cabeçalho e menu das outras, mais a mensagem "Página não encontrada." Um item inexistente (`?pagina=produto&id=99`) também devolve 404.

**A rota JSON:** `curl.exe -i "http://localhost:8000/?formato=json"` (a lista continua com os outros produtos e os pedidos; aqui aparece só o início)

```text
HTTP/1.1 200 OK
Host: localhost:8000
Date: Tue, 03 Nov 2026 22:11:27 GMT
Connection: close
X-Powered-By: PHP/8.3.6
Content-Type: application/json; charset=utf-8

{
    "produtos": [
        {
            "id": 1,
            "nome": "Pão de queijo",
            "tipo": "Unitário",
            "preco": 4
        },
        ...
```

Status `200 OK` e `Content-Type: application/json; charset=utf-8`, como pede o enunciado. O corpo foi gerado com `paraArray()` e `json_encode`.

**JSON de um item que não existe:** `curl.exe -i "http://localhost:8000/?pagina=produto&id=99&formato=json"`

```text
HTTP/1.1 404 Not Found
Host: localhost:8000
Date: Tue, 03 Nov 2026 22:11:27 GMT
Connection: close
X-Powered-By: PHP/8.3.6
Content-Type: application/json; charset=utf-8

{
    "erro": "Produto não encontrado."
}
```

O 404 também vale para o JSON, com a mensagem de erro no corpo.

**O formulário com um dado inválido:** print da página com a mensagem de recusa (`docs/tela-erro-formulario.png`) e o log do servidor.

Mensagens que o formulário mostrou nos envios inválidos (cliente vazio e quantidade fracionada num produto unitário):

```text
<p class="erro">O nome do cliente não pode ficar vazio.</p>
<p class="erro">Produto unitário só aceita quantidade inteira.</p>
```

Envio válido (Ana, 2 pães de queijo e 1,5 kg de carne de sol):

```text
Pedido registrado para Ana
Total</th><th>R$ 80,00
```

Log do servidor durante os testes desta seção (as três últimas linhas são os envios do formulário):

```text
[Nov 3 22:11:27 2026] 127.0.0.1 [200]: GET /?pagina=produtos
[Nov 3 22:11:27 2026] 127.0.0.1 [404]: GET /?pagina=inexistente
[Nov 3 22:11:27 2026] 127.0.0.1 [200]: GET /?formato=json
[Nov 3 22:11:27 2026] 127.0.0.1 [404]: GET /?pagina=produto&id=99&formato=json
[Nov 3 22:11:27 2026] 127.0.0.1 [200]: POST /?pagina=novo-pedido
[Nov 3 22:11:27 2026] 127.0.0.1 [200]: POST /?pagina=novo-pedido
[Nov 3 22:11:27 2026] 127.0.0.1 [200]: POST /?pagina=novo-pedido
```

O formulário responde `200` tanto na recusa quanto na aceitação: o que muda é o conteúdo da página, que mostra a mensagem da exceção ou o resumo do pedido.

**Segurança (W4):** um `<script>` digitado no campo cliente volta ao formulário escapado (`&lt;script&gt;`) e nunca é executado, e `?pagina=<script>` devolve 404 sem o texto cru na página.
