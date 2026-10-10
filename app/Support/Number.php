<?php

namespace App\Support;

use Illuminate\Http\Request;

class Number
{
    /**
     * Converte valores digitados no padrão brasileiro ("1.234,56") ou decimal ("1234.56")
     * para float. Retorna 0.0 quando o valor é inválido/vazio.
     */
    public static function money(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        $value = trim((string) $value);
        $value = str_replace(['R$', ' '], '', $value);

        // 1.234,56 -> 1234.56
        if (str_contains($value, ',') && preg_match('/^(\d{1,3}(\.\d{3})*),\d{1,2}$/', $value)) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? round((float) $value, 2) : 0.0;
    }

    public static function inteiro(mixed $value, int $padrao = 0): int
    {
        if ($value === null || $value === '') {
            return $padrao;
        }

        return (int) str_replace(['.', ','], ['', ''], is_numeric($value) ? (string) $value : trim((string) $value));
    }

    public static function formatar(float|int|string|null $valor): string
    {
        return number_format((float) $valor, 2, ',', '.');
    }

    /**
     * Normaliza campos de um formulário ANTES da validação, para que o sistema aceite
     * tanto o padrão brasileiro ("1.234,56") quanto o decimal ("1234.56").
     *
     * Campos vazios não são tocados: assim as regras "required" continuam valendo.
     *
     * @param  array<int, string>  $dinheiro
     * @param  array<int, string>  $inteiros
     */
    public static function normalizarRequest(Request $request, array $dinheiro = [], array $inteiros = []): void
    {
        $novos = [];

        foreach ($dinheiro as $campo) {
            $bruto = $request->input($campo);

            if (is_string($bruto) && trim($bruto) !== '') {
                $novos[$campo] = self::money($bruto);
            }
        }

        foreach ($inteiros as $campo) {
            $bruto = $request->input($campo);

            if (is_string($bruto) && trim($bruto) !== '') {
                $novos[$campo] = self::inteiro($bruto);
            }
        }

        if ($novos !== []) {
            $request->merge($novos);
        }
    }

    /**
     * Idem, para listas de itens (ex.: itens[0][valor_unitario] de uma venda).
     *
     * @param  array<int, string>  $dinheiro
     * @param  array<int, string>  $inteiros
     */
    public static function normalizarItens(Request $request, string $chave, array $dinheiro = [], array $inteiros = []): void
    {
        $itens = $request->input($chave);

        if (! is_array($itens)) {
            return;
        }

        foreach ($itens as $indice => $item) {
            if (! is_array($item)) {
                continue;
            }

            foreach ($dinheiro as $campo) {
                if (isset($item[$campo]) && is_string($item[$campo]) && trim($item[$campo]) !== '') {
                    $itens[$indice][$campo] = self::money($item[$campo]);
                }
            }

            foreach ($inteiros as $campo) {
                if (isset($item[$campo]) && is_string($item[$campo]) && trim($item[$campo]) !== '') {
                    $itens[$indice][$campo] = self::inteiro($item[$campo]);
                }
            }
        }

        $request->merge([$chave => $itens]);
    }

    /** Remove máscara de CPF/CNPJ/telefone, mantendo apenas dígitos. */
    public static function somenteDigitos(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $digitos = preg_replace('/\D/', '', $valor);

        return $digitos === '' ? null : $digitos;
    }
}
