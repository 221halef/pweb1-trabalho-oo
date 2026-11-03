<?php

declare(strict_types=1);

require_once __DIR__ . '/src/ProdutoUnitario.php';
require_once __DIR__ . '/src/ProdutoPorPeso.php';
require_once __DIR__ . '/src/ProdutoPerecivel.php';
require_once __DIR__ . '/src/ItemPedido.php';
require_once __DIR__ . '/src/Pedido.php';

date_default_timezone_set('America/Fortaleza');

function dataEmDias(int $dias): string
{
    return date('Y-m-d', strtotime(sprintf('%+d days', $dias)));
}

function formatar(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

$pao = new ProdutoUnitario('Pão de queijo', 4.0);
$carne = new ProdutoPorPeso('Carne de sol', 48.0, 0.5);
$iogurte = new ProdutoPerecivel('Iogurte', 10.0, dataEmDias(1));
$queijo = new ProdutoPerecivel('Queijo coalho', 30.0, dataEmDias(10));

echo "=== Teste 1: polimorfismo (mesmo método, respostas diferentes) ===\n";
$produtos = [$pao, $carne, $iogurte, $queijo];

foreach ($produtos as $produto) {
    echo "{$produto->getNome()} [{$produto->getTipo()}]: 2 x " . formatar($produto->getPreco())
        . ' = ' . formatar($produto->calcularPreco(2)) . "\n";
}

echo "\n=== Teste 2: sobrescrita de calcularPreco() no perecível ===\n";
echo "Iogurte (vence em 1 dia), 1 un: " . formatar($iogurte->calcularPreco(1)) . " (esperado R$ 7,00)\n";
echo "Queijo (vence em 10 dias), 1 un: " . formatar($queijo->calcularPreco(1)) . " (esperado R$ 30,00)\n";

echo "\n=== Teste 3: composição (pedido com itens e total) ===\n";
$pedido = new Pedido('Maria');
$pedido->adicionarItem(new ItemPedido($pao, 3));
$pedido->adicionarItem(new ItemPedido($carne, 1.5));
$pedido->adicionarItem(new ItemPedido($iogurte, 2));

foreach ($pedido->getItens() as $item) {
    echo "{$item->getProduto()->getNome()} x{$item->getQuantidade()} = "
        . formatar($item->calcularSubtotal()) . "\n";
}
echo "Total de {$pedido->getCliente()}: " . formatar($pedido->calcularTotal()) . " (esperado R$ 98,00)\n";

echo "\n=== Teste 4: regras de Produto (nome, preço e quantidade) ===\n";
try {
    new ProdutoUnitario('   ', 5.0);
    echo "ERRO: o nome vazio foi aceito\n";
} catch (Exception $e) {
    echo "OK, recusado: {$e->getMessage()}\n";
}

try {
    new ProdutoUnitario('Rapadura', 0.0);
    echo "ERRO: o preço zero foi aceito\n";
} catch (Exception $e) {
    echo "OK, recusado: {$e->getMessage()}\n";
}

try {
    new ItemPedido($pao, 0);
    echo "ERRO: a quantidade zero foi aceita\n";
} catch (Exception $e) {
    echo "OK, recusado: {$e->getMessage()}\n";
}

echo "\n=== Teste 5: regras das filhas (unitário e por peso) ===\n";
try {
    new ItemPedido($pao, 1.5);
    echo "ERRO: a quantidade fracionada foi aceita no produto unitário\n";
} catch (Exception $e) {
    echo "OK, recusado: {$e->getMessage()}\n";
}

try {
    new ItemPedido($carne, 0.2);
    echo "ERRO: a quantidade abaixo do mínimo foi aceita\n";
} catch (Exception $e) {
    echo "OK, recusado: {$e->getMessage()}\n";
}

try {
    new ProdutoPorPeso('Queijo de manteiga', 40.0, 0.0);
    echo "ERRO: o peso mínimo zero foi aceito\n";
} catch (Exception $e) {
    echo "OK, recusado: {$e->getMessage()}\n";
}

echo "\n=== Teste 6: regras do produto perecível (validade) ===\n";
try {
    new ProdutoPerecivel('Leite', 6.0, 'amanhã');
    echo "ERRO: a validade inválida foi aceita\n";
} catch (Exception $e) {
    echo "OK, recusado: {$e->getMessage()}\n";
}

try {
    new ProdutoPerecivel('Requeijão', 12.0, dataEmDias(-1));
    echo "ERRO: o produto vencido foi aceito\n";
} catch (Exception $e) {
    echo "OK, recusado: {$e->getMessage()}\n";
}

echo "\n=== Teste 7: regras do pedido (cliente e produto repetido) ===\n";
try {
    new Pedido('   ');
    echo "ERRO: o cliente vazio foi aceito\n";
} catch (Exception $e) {
    echo "OK, recusado: {$e->getMessage()}\n";
}

try {
    $pedido->adicionarItem(new ItemPedido($pao, 1));
    echo "ERRO: o produto repetido foi aceito\n";
} catch (Exception $e) {
    echo "OK, recusado: {$e->getMessage()}\n";
}

echo "\n=== Teste 8: paraArray() e JSON do pedido ===\n";
echo json_encode($pedido->paraArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
