<?php

declare(strict_types=1);

require_once __DIR__ . '/Produto.php';

class ItemPedido
{
    private Produto $produto;
    private float $quantidade;

    public function __construct(Produto $produto, float $quantidade)
    {
        $produto->validarQuantidade($quantidade);

        $this->produto = $produto;
        $this->quantidade = $quantidade;
    }

    public function getProduto(): Produto
    {
        return $this->produto;
    }

    public function getQuantidade(): float
    {
        return $this->quantidade;
    }

    public function calcularSubtotal(): float
    {
        return $this->produto->calcularPreco($this->quantidade);
    }

    public function paraArray(): array
    {
        return [
            'produto' => $this->produto->paraArray(),
            'quantidade' => $this->quantidade,
            'subtotal' => $this->calcularSubtotal(),
        ];
    }
}
