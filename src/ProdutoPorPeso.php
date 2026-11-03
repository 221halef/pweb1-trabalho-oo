<?php

declare(strict_types=1);

require_once __DIR__ . '/Produto.php';

class ProdutoPorPeso extends Produto
{
    private float $pesoMinimo;

    public function __construct(string $nome, float $preco, float $pesoMinimo)
    {
        parent::__construct($nome, $preco);

        if ($pesoMinimo <= 0) {
            throw new Exception('O peso mínimo deve ser maior que zero.');
        }

        $this->pesoMinimo = $pesoMinimo;
    }

    public function getPesoMinimo(): float
    {
        return $this->pesoMinimo;
    }

    public function getTipo(): string
    {
        return 'Por peso';
    }

    public function validarQuantidade(float $quantidade): void
    {
        parent::validarQuantidade($quantidade);

        if ($quantidade < $this->pesoMinimo) {
            $minimo = str_replace('.', ',', (string) $this->pesoMinimo);
            throw new Exception("A compra mínima de {$this->nome} é {$minimo} kg.");
        }
    }

    public function paraArray(): array
    {
        return array_merge(parent::paraArray(), [
            'peso_minimo' => $this->pesoMinimo,
        ]);
    }
}
