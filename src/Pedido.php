<?php

declare(strict_types=1);

require_once __DIR__ . '/ItemPedido.php';

class Pedido
{
    private string $cliente;
    private array $itens = [];

    public function __construct(string $cliente)
    {
        if (trim($cliente) === '') {
            throw new Exception('O nome do cliente não pode ficar vazio.');
        }

        $this->cliente = trim($cliente);
    }

    public function getCliente(): string
    {
        return $this->cliente;
    }

    public function getItens(): array
    {
        return $this->itens;
    }

    public function adicionarItem(ItemPedido $item): void
    {
        $nome = $item->getProduto()->getNome();

        foreach ($this->itens as $existente) {
            if (strcasecmp($existente->getProduto()->getNome(), $nome) === 0) {
                throw new Exception("O produto {$nome} já está no pedido.");
            }
        }

        $this->itens[] = $item;
    }

    public function calcularTotal(): float
    {
        $total = 0.0;

        foreach ($this->itens as $item) {
            $total += $item->calcularSubtotal();
        }

        return round($total, 2);
    }

    public function paraArray(): array
    {
        return [
            'cliente' => $this->cliente,
            'itens' => array_map(
                fn (ItemPedido $item): array => $item->paraArray(),
                $this->itens
            ),
            'total' => $this->calcularTotal(),
        ];
    }
}
