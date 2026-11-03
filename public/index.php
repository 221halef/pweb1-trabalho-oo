<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/ProdutoUnitario.php';
require_once __DIR__ . '/../src/ProdutoPorPeso.php';
require_once __DIR__ . '/../src/ProdutoPerecivel.php';
require_once __DIR__ . '/../src/ItemPedido.php';
require_once __DIR__ . '/../src/Pedido.php';

date_default_timezone_set('America/Fortaleza');

function e(string|int|float $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function moeda(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function quantidade(float $valor): string
{
    return rtrim(rtrim(number_format($valor, 3, ',', ''), '0'), ',');
}

function dataBr(string $data): string
{
    return implode('/', array_reverse(explode('-', $data)));
}

function dataEmDias(int $dias): string
{
    return date('Y-m-d', strtotime(sprintf('%+d days', $dias)));
}

function unidade(array $produto): string
{
    return isset($produto['peso_minimo']) ? 'kg' : 'un';
}

function detalhesProduto(array $produto): string
{
    if (isset($produto['peso_minimo'])) {
        return 'Compra mínima: ' . quantidade($produto['peso_minimo']) . ' kg';
    }

    if (isset($produto['validade'])) {
        $texto = 'Validade: ' . dataBr($produto['validade']);
        if ($produto['em_promocao']) {
            $texto .= ' (em promoção)';
        }

        return $texto;
    }

    return 'Vendido por unidade';
}

$produtos = [
    1 => new ProdutoUnitario('Pão de queijo', 4.0),
    2 => new ProdutoUnitario('Rapadura', 3.5),
    3 => new ProdutoPorPeso('Carne de sol', 48.0, 0.5),
    4 => new ProdutoPorPeso('Queijo de manteiga', 40.0, 0.3),
    5 => new ProdutoPerecivel('Iogurte', 10.0, dataEmDias(1)),
    6 => new ProdutoPerecivel('Queijo coalho', 30.0, dataEmDias(10)),
];

$maria = new Pedido('Maria');
$maria->adicionarItem(new ItemPedido($produtos[1], 3));
$maria->adicionarItem(new ItemPedido($produtos[3], 1.5));
$maria->adicionarItem(new ItemPedido($produtos[5], 2));

$jose = new Pedido('José');
$jose->adicionarItem(new ItemPedido($produtos[6], 1));
$jose->adicionarItem(new ItemPedido($produtos[2], 5));

$pedidos = [1 => $maria, 2 => $jose];

$listaProdutos = [];
foreach ($produtos as $codigo => $produto) {
    $listaProdutos[$codigo] = ['id' => $codigo] + $produto->paraArray();
}

$listaPedidos = [];
foreach ($pedidos as $codigo => $pedido) {
    $listaPedidos[$codigo] = ['id' => $codigo] + $pedido->paraArray();
}

$pagina = $_GET['pagina'] ?? 'inicio';
if (!is_string($pagina)) {
    $pagina = '';
}

$formato = ($_GET['formato'] ?? '') === 'json' ? 'json' : 'html';

$id = 0;
if (isset($_GET['id']) && is_string($_GET['id']) && ctype_digit($_GET['id'])) {
    $id = (int) $_GET['id'];
}

$status = 200;
$mensagemErro = '';
$dados = [];

switch ($pagina) {
    case 'inicio':
        $dados = [
            'produtos' => array_values($listaProdutos),
            'pedidos' => array_values($listaPedidos),
        ];
        break;
    case 'produtos':
        $dados = ['produtos' => array_values($listaProdutos)];
        break;
    case 'produto':
        if (isset($listaProdutos[$id])) {
            $dados = $listaProdutos[$id];
        } else {
            $status = 404;
            $mensagemErro = 'Produto não encontrado.';
        }
        break;
    case 'pedidos':
        $dados = ['pedidos' => array_values($listaPedidos)];
        break;
    case 'pedido':
        if (isset($listaPedidos[$id])) {
            $dados = $listaPedidos[$id];
        } else {
            $status = 404;
            $mensagemErro = 'Pedido não encontrado.';
        }
        break;
    default:
        $status = 404;
        $mensagemErro = 'Página não encontrada.';
}

if ($formato === 'json') {
    if ($status === 404) {
        $dados = ['erro' => $mensagemErro];
    }

    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

http_response_code($status);

$menu = [
    'inicio' => 'Início',
    'produtos' => 'Produtos',
    'pedidos' => 'Pedidos',
];

$menuAtivo = match ($pagina) {
    'inicio' => 'inicio',
    'produto', 'produtos' => 'produtos',
    'pedido', 'pedidos' => 'pedidos',
    default => '',
};

$titulo = match (true) {
    $status === 404 => 'Não encontrado',
    $pagina === 'produto' => $dados['nome'],
    $pagina === 'pedido' => 'Pedido ' . $dados['id'],
    default => $menu[$menuAtivo],
};

$parametrosJson = ['pagina' => $pagina, 'formato' => 'json'];
if ($id > 0) {
    $parametrosJson['id'] = $id;
}
$linkJson = '?' . http_build_query($parametrosJson);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo) ?> | Feira Digital do Sertão</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <header class="topo">
    <h1>Feira Digital do Sertão</h1>
    <nav class="menu">
      <?php foreach ($menu as $chave => $rotulo): ?>
        <a href="?pagina=<?= e($chave) ?>"<?= $chave === $menuAtivo ? ' class="ativo"' : '' ?>><?= e($rotulo) ?></a>
      <?php endforeach; ?>
    </nav>
  </header>

  <main class="conteudo">
    <?php if ($status === 404): ?>
      <section class="aviso">
        <h2>404</h2>
        <p><?= e($mensagemErro) ?></p>
        <p><a href="?pagina=inicio">Voltar ao início</a></p>
      </section>

    <?php elseif ($pagina === 'inicio'): ?>
      <section>
        <h2>Bem-vindo à feira</h2>
        <p>Produtos da agricultura familiar do sertão, vendidos por unidade, por peso ou com validade curta.</p>
        <p>
          A feira tem <strong><?= e(count($listaProdutos)) ?></strong> produtos no catálogo
          e <strong><?= e(count($listaPedidos)) ?></strong> pedidos de exemplo.
        </p>
        <p>
          <a class="botao" href="?pagina=produtos">Ver produtos</a>
          <a class="botao secundario" href="?pagina=pedidos">Ver pedidos</a>
        </p>
      </section>

    <?php elseif ($pagina === 'produtos'): ?>
      <section>
        <h2>Produtos</h2>
        <table>
          <thead>
            <tr><th>Produto</th><th>Tipo</th><th>Preço</th><th>Detalhes</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach ($listaProdutos as $produto): ?>
              <tr>
                <td><?= e($produto['nome']) ?></td>
                <td><?= e($produto['tipo']) ?></td>
                <td><?= e(moeda($produto['preco'])) ?>/<?= e(unidade($produto)) ?></td>
                <td><?= e(detalhesProduto($produto)) ?></td>
                <td><a href="?pagina=produto&amp;id=<?= e($produto['id']) ?>">Ver</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>

    <?php elseif ($pagina === 'produto'): ?>
      <section>
        <h2><?= e($dados['nome']) ?></h2>
        <dl class="ficha">
          <dt>Código</dt><dd><?= e($dados['id']) ?></dd>
          <dt>Tipo</dt><dd><?= e($dados['tipo']) ?></dd>
          <dt>Preço</dt><dd><?= e(moeda($dados['preco'])) ?>/<?= e(unidade($dados)) ?></dd>
          <dt>Detalhes</dt><dd><?= e(detalhesProduto($dados)) ?></dd>
        </dl>
        <p><a href="?pagina=produtos">Voltar aos produtos</a></p>
      </section>

    <?php elseif ($pagina === 'pedidos'): ?>
      <section>
        <h2>Pedidos</h2>
        <table>
          <thead>
            <tr><th>Pedido</th><th>Cliente</th><th>Itens</th><th>Total</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach ($listaPedidos as $pedido): ?>
              <tr>
                <td>#<?= e($pedido['id']) ?></td>
                <td><?= e($pedido['cliente']) ?></td>
                <td><?= e(count($pedido['itens'])) ?></td>
                <td><?= e(moeda($pedido['total'])) ?></td>
                <td><a href="?pagina=pedido&amp;id=<?= e($pedido['id']) ?>">Ver</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>

    <?php elseif ($pagina === 'pedido'): ?>
      <section>
        <h2>Pedido #<?= e($dados['id']) ?> de <?= e($dados['cliente']) ?></h2>
        <table>
          <thead>
            <tr><th>Produto</th><th>Tipo</th><th>Quantidade</th><th>Subtotal</th></tr>
          </thead>
          <tbody>
            <?php foreach ($dados['itens'] as $item): ?>
              <tr>
                <td><?= e($item['produto']['nome']) ?></td>
                <td><?= e($item['produto']['tipo']) ?></td>
                <td><?= e(quantidade($item['quantidade'])) ?> <?= e(unidade($item['produto'])) ?></td>
                <td><?= e(moeda($item['subtotal'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr><th colspan="3">Total</th><th><?= e(moeda($dados['total'])) ?></th></tr>
          </tfoot>
        </table>
        <p><a href="?pagina=pedidos">Voltar aos pedidos</a></p>
      </section>
    <?php endif; ?>

    <?php if ($status === 200): ?>
      <p class="json"><a href="<?= e($linkJson) ?>">Ver esta página em JSON</a></p>
    <?php endif; ?>
  </main>

  <footer class="rodape">
    <p>Feira Digital do Sertão | Programação Web I | IFCE Campus Boa Viagem</p>
  </footer>
</body>
</html>
