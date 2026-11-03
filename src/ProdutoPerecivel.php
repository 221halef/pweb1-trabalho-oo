<?php

declare(strict_types=1);

require_once __DIR__ . '/Produto.php';

class ProdutoPerecivel extends Produto
{
    private const DIAS_PROMOCAO = 2;
    private const DESCONTO = 0.30;

    private string $validade;

    public function __construct(string $nome, float $preco, string $validade)
    {
        parent::__construct($nome, $preco);

        $data = DateTimeImmutable::createFromFormat('!Y-m-d', $validade, self::fuso());
        if ($data === false || $data->format('Y-m-d') !== $validade) {
            throw new Exception('A validade deve ser uma data válida no formato AAAA-MM-DD.');
        }
        if ($data < self::hoje()) {
            throw new Exception('O produto perecível não pode estar vencido.');
        }

        $this->validade = $validade;
    }

    public function getValidade(): string
    {
        return $this->validade;
    }

    public function getTipo(): string
    {
        return 'Perecível';
    }

    public function calcularPreco(float $quantidade): float
    {
        $total = parent::calcularPreco($quantidade);

        if ($this->diasParaVencer() <= self::DIAS_PROMOCAO) {
            return round($total * (1 - self::DESCONTO), 2);
        }

        return $total;
    }

    public function paraArray(): array
    {
        return array_merge(parent::paraArray(), [
            'validade' => $this->validade,
            'em_promocao' => $this->diasParaVencer() <= self::DIAS_PROMOCAO,
        ]);
    }

    private function diasParaVencer(): int
    {
        $data = DateTimeImmutable::createFromFormat('!Y-m-d', $this->validade, self::fuso());

        return (int) self::hoje()->diff($data)->days;
    }

    private static function fuso(): DateTimeZone
    {
        return new DateTimeZone('America/Fortaleza');
    }

    private static function hoje(): DateTimeImmutable
    {
        return new DateTimeImmutable('today', self::fuso());
    }
}
