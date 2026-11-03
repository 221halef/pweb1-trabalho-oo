<?php

declare(strict_types=1);

class Produto
{
    protected string $nome;
    protected float $preco;

    public function __construct(string $nome, float $preco)
    {
        if (trim($nome) === '') {
            throw new Exception('O nome do produto não pode ficar vazio.');
        }
        if ($preco <= 0) {
            throw new Exception('O preço do produto deve ser maior que zero.');
        }

        $this->nome = trim($nome);
        $this->preco = $preco;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function getPreco(): float
    {
        return $this->preco;
    }

    public function getTipo(): string
    {
        return 'Produto';
    }

    public function validarQuantidade(float $quantidade): void
    {
        if ($quantidade <= 0) {
            throw new Exception('A quantidade deve ser maior que zero.');
        }
    }

    public function calcularPreco(float $quantidade): float
    {
        $this->validarQuantidade($quantidade);

        return round($this->preco * $quantidade, 2);
    }

    public function paraArray(): array
    {
        return [
            'nome' => $this->nome,
            'tipo' => $this->getTipo(),
            'preco' => $this->preco,
        ];
    }
}
