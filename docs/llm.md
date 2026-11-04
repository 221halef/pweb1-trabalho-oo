# Diário de uso de LLM

## Interação 1

- **Ferramenta e data:** Claude
- **Prompt:**

```text
Estou fazendo um trabalho de Programação Web I em PHP orientado a objetos (tema: feira de produtos). Na classe Produto usei atributos protected, e na ProdutoPorPeso usei private para o pesoMinimo. Explique, usando exatamente esse meu código como exemplo, a diferença entre protected e private e por que a escolha foi diferente em cada classe. Não reescreva o código. Responda em até 10 linhas e termine com uma pergunta para eu testar se entendi.
```

- **Resumo da resposta:** `protected` deixa a própria classe e as filhas usarem o atributo; `private` deixa só a própria classe. `nome` e `preco` são protected porque as filhas precisam deles (a ProdutoPorPeso usa `$this->nome` na mensagem de erro); `pesoMinimo` é private porque é uma regra só dela. Terminou com uma pergunta de verificação.

```text
Resposta:

Em Produto.php, `nome` e `preco` são protected (linhas 7 e 8): a própria
Produto e as filhas conseguem usar esses atributos direto, mas o código de
fora, como o index.php, não consegue.
Em ProdutoPorPeso.php, `pesoMinimo` é private (linha 9): só a ProdutoPorPeso
enxerga esse atributo. Quem está fora lê pelo getPesoMinimo().
A escolha mudou porque `nome` é um dado que a filha precisa usar: na linha 38
ela escreve $this->nome na mensagem de erro, e isso só funciona por ser
protected. O `pesoMinimo` é uma regra que só existe no produto por peso; se
fosse protected, outra classe filha poderia mudar o valor e quebrar a regra.
Dica: comece com private e só abra para protected quando uma filha precisar.

Teste: se na ProdutoUnitario você escrevesse $this->preco, funcionaria?
E $this->pesoMinimo? Por quê?
```

## Interação 2

- **Ferramenta e data:** Claude
- **Prompt:**

```text
Estou rodando o site com "php -S localhost:8000 -t public". Esperava ver a página com estilo, mas o navegador mostra sem CSS, e o console do DevTools diz: "The stylesheet http://localhost:8000/public/estilo.css was not loaded because its MIME type, 'text/html', is not 'text/css'". Já confirmei que o arquivo public/estilo.css existe. O que essa mensagem quer dizer e quais são as 2 ou 3 causas mais prováveis? Não me dê a correção pronta ainda: me diga como verificar cada causa, para eu descobrir qual é a minha.
```

- **Resumo da resposta:** Explicou que o navegador pediu um CSS e o servidor devolveu HTML (provavelmente uma página de erro 404), então recusou usar. Listou 3 causas prováveis (caminho errado no `href`, pasta raiz do servidor diferente da esperada, servidor com script roteador devolvendo HTML) e como verificar cada uma, sem dar a correção pronta.

```text
Resposta:

O que a mensagem quer dizer
O navegador pediu http://localhost:8000/public/estilo.css esperando receber CSS
(Content-Type: text/css). O servidor respondeu com HTML (text/html), muito
provavelmente uma página de erro 404. Como o tipo não bate, o navegador recusa
aplicar. Ou seja: o problema não é o conteúdo do CSS, é o que o servidor
devolveu para essa URL.

Pista importante: com `-t public`, a pasta public já é a raiz do site. O
arquivo public/estilo.css deveria ser acessado como /estilo.css. A URL do erro
tem /public/ a mais.

Causa 1: o href do <link> está com o caminho errado
Como verificar: abra o código-fonte da página (Ctrl+U) e olhe o atributo href
do <link>. Compare com a URL que aparece no erro. Se o href começa com
"public/" ou "/public/", a URL pedida será /public/estilo.css.

Causa 2: a raiz do servidor não é a pasta que você pensa
Como verificar: olhe o terminal onde o php -S está rodando. A primeira linha
mostra "Document root is ...". Confira se é a pasta public do projeto. Veja
também de qual pasta você executou o comando (pwd): o -t public é relativo a
ela.

Causa 3: algo está respondendo HTML para qualquer URL
Pode ser um script roteador passado ao php -S, ou um redirecionamento no seu
index.php (ele não deveria responder por arquivos que existem).
Como verificar: no terminal do servidor, veja a linha da requisição, por
exemplo "[404]: GET /public/estilo.css". Depois rode
curl -i http://localhost:8000/estilo.css e
curl -i http://localhost:8000/public/estilo.css e compare o status e o
Content-Type de cada um. Na aba Network do DevTools, clique na requisição do
CSS e leia a aba Response: se aparecer HTML, é uma página de erro.

Me diga o que você encontrou em cada teste e eu te ajudo a corrigir.
```

## Interação 3

- **Ferramenta e data:** Claude
- **Prompt:**

```text
Segue o código da minha classe Pedido (colado abaixo). Os requisitos são: construtor, atributos tipados e privados, método para adicionar item, método que calcula o total, regras recusadas com throw new Exception e paraArray(). Revise apenas contra esses requisitos e liste o que está faltando ou pode dar erro, em ordem de importância. Não altere o código: aponte a linha e explique o problema. Se algo estiver correto, diga que está.
```

- **Resumo da resposta:** Todos os requisitos estão atendidos. Apontou 2 pontos de melhoria: a comparação de nomes com `strcasecmp` não ignora maiúsculas com acento (linha 36) e a falta de decisão sobre pedido vazio (linhas 44 a 53). Sugeriu documentar o tipo dos itens do array (linha 10).

```text
Resposta:

Está correto (atende ao requisito):
- Construtor: linha 12, recusa cliente vazio com throw new Exception (linha 15).
- Atributos tipados e privados: linhas 9 e 10 (string e array).
- adicionarItem(): linha 31, recusa produto repetido com throw new Exception
  (linha 37).
- calcularTotal(): linha 44, soma os subtotais e arredonda no final.
- paraArray(): linha 55, devolve cliente, itens (via paraArray de cada item)
  e total.
- getItens() (linha 26) devolve uma cópia do array e o ItemPedido não tem
  setters, então o encapsulamento está seguro.

O que pode dar erro, em ordem de importância:
1. Linha 36: strcasecmp não entende acentos em UTF-8. "Maçã" e "MAÇÃ" são
   tratados como produtos diferentes e o pedido aceita os dois, mesmo a regra
   dizendo que não pode repetir. Sem acento ("Maca" e "MACA") funciona. Para
   comparar com acento, use mb_strtolower nos dois lados (com 'UTF-8').
2. Linhas 44 a 53: um pedido sem itens retorna total 0.0 e paraArray() com
   itens vazio. Não é erro, mas decida se isso é permitido; se não for,
   é uma regra que mereceria um throw new Exception (por exemplo, ao
   finalizar o pedido).
3. Linha 10: `private array $itens` não diz que o array guarda ItemPedido. O PHP
   não deixa tipar isso, mas um comentário /** @var ItemPedido[] */ ajuda o
   leitor e o editor.

Nada falta dos requisitos listados.
```

## Interação 4

- **Ferramenta e data:** Claude
- **Prompt:**

```text
Escrevi esta justificativa da herança para o meu trabalho (colada abaixo). Quero que ela explique por que Produto é a classe mãe e por que "um produto por peso é um produto" vale para as três filhas. Aponte o que está confuso ou faltando e sugira como melhorar cada parágrafo.
```

## Interação 5

- **Ferramenta e data:** Claude
- **Prompt:**

```text
No meu index.php vou criar os objetos com os dados do formulário ($_POST). O arquivo usa declare(strict_types=1) e as minhas classes recebem float no construtor (ex.: ProdutoUnitario($nome, float $preco)) e lançam Exception nas regras. O que pode dar errado quando o preço chega do $_POST, e meu try/catch (Exception) vai pegar o problema? Não escreva o código completo: explique o que acontece e como eu posso verificar isso no terminal.
```

- **Resumo da resposta:** Explicou que tudo em `$_POST` é string, e que com `strict_types=1` no `index.php` passar string a um parâmetro `float` lança `TypeError`, que não é `Exception` e portanto escapa do `catch (Exception)`. Também alertou para a vírgula decimal ("1,5") e deu um teste de terminal.

```text
Resposta:

O que acontece
Tudo que vem de $_POST é string (até um campo number do HTML). Como o
index.php tem declare(strict_types=1), chamar new ProdutoUnitario($nome, "5.5")
é recusado: o PHP lança um TypeError ("must be of type float, string given").
O strict_types vale para o arquivo que FAZ a chamada, então o index.php é quem
manda aqui, não a classe.

Por que o seu catch não pega
TypeError não herda de Exception: ele herda de Error. Os dois implementam
Throwable. Um catch (Exception $e) deixa o TypeError passar, e o usuário vê
um erro fatal em vez da mensagem de recusa do formulário.

Outros problemas do mesmo tipo
- Campo vazio ou texto ("abc"): vira string que não é número.
- Vírgula decimal: um usuário brasileiro pode digitar "5,5". Se você converter
  com (float), "5,5" vira 5.0 sem nenhum aviso, e o preço fica errado.

Como verificar no terminal
1. Crie um arquivo de teste com declare(strict_types=1), dê require_once na
   ProdutoUnitario e rode new ProdutoUnitario('Ovo', '5.5'): você deve ver o
   TypeError.
2. Rode o mesmo com um preço 5.5 (número) e veja funcionar.
3. Teste (float) "5,5" com php -r 'var_dump((float) "5,5");' e veja o resultado.

Caminhos possíveis (você escolhe)
- Converter e validar no index.php antes de criar o objeto (is_numeric depois
  de trocar a vírgula por ponto), e recusar com uma mensagem amigável.
- Capturar também TypeError no catch.
Os dois podem ser combinados; o primeiro dá mensagem melhor ao usuário.
```

