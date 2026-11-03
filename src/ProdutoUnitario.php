<?php

declare(strict_types=1);

require_once __DIR__ . '/Produto.php';

class ProdutoUnitario extends Produto
{
    public function __construct(string $nome, float $preco)
    {
        parent::__construct($nome, $preco);
    }

    public function getTipo(): string
    {
        return 'Unitário';
    }

    public function validarQuantidade(float $quantidade): void
    {
        parent::validarQuantidade($quantidade);

        if (fmod($quantidade, 1.0) !== 0.0) {
            throw new Exception('Produto unitário só aceita quantidade inteira.');
        }
    }
}
