<?php

namespace App\Support;

/**
 * Leitura/escrita simples do arquivo .env.
 *
 * Usado pelo instalador web, pelo comando artisan de instalação e na
 * inicialização automática do sistema (quando o .env ainda não existe).
 */
class EnvEditor
{
    public static function path(): string
    {
        return base_path('.env');
    }

    public static function examplePath(): string
    {
        return base_path('.env.example');
    }

    public static function exists(): bool
    {
        return is_file(self::path());
    }

    public static function isWritable(): bool
    {
        return self::exists()
            ? is_writable(self::path())
            : is_writable(base_path());
    }

    /**
     * Cria o .env a partir do .env.example (se ainda não existir) aplicando os valores informados.
     */
    public static function createFromExample(array $values = []): bool
    {
        if (self::exists()) {
            return self::setValues($values);
        }

        if (! is_file(self::examplePath())) {
            return false;
        }

        if (! @copy(self::examplePath(), self::path())) {
            return false;
        }

        @chmod(self::path(), 0644);

        return self::setValues($values);
    }

    /**
     * Atualiza (ou acrescenta) pares CHAVE=valor no .env.
     */
    public static function setValues(array $values): bool
    {
        if (empty($values)) {
            return self::exists();
        }

        $contents = self::exists() ? (string) file_get_contents(self::path()) : '';

        foreach ($values as $key => $value) {
            $line = $key.'='.self::formatValue($value);

            if (preg_match('/^'.preg_quote($key, '/').'=.*$/m', $contents)) {
                $contents = preg_replace('/^'.preg_quote($key, '/').'=.*$/m', $line, $contents);
            } else {
                $contents = rtrim($contents, "\n")."\n".$line."\n";
            }
        }

        return (bool) @file_put_contents(self::path(), $contents);
    }

    public static function get(string $key): ?string
    {
        if (! self::exists()) {
            return null;
        }

        $contents = (string) file_get_contents(self::path());

        if (preg_match('/^'.preg_quote($key, '/').'=(.*)$/m', $contents, $m)) {
            return trim($m[1], " \t\n\r\0\x0B\"'");
        }

        return null;
    }

    public static function generateKey(): string
    {
        return 'base64:'.base64_encode(random_bytes(32));
    }

    /**
     * Garante que existe um .env com APP_KEY definida. Retorna a chave em uso.
     */
    public static function ensureAppKey(): ?string
    {
        self::createFromExample();

        $key = self::get('APP_KEY');

        if (empty($key)) {
            $key = self::generateKey();
            self::setValues(['APP_KEY' => $key]);
        }

        return $key ?: null;
    }

    protected static function formatValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        $value = (string) $value;

        if ($value === '') {
            return '""';
        }

        // Valores com espaços, # ou ${...} precisam de aspas para o dotenv.
        if (preg_match('/\s|#|\$|"/', $value)) {
            return '"'.str_replace('"', '\"', $value).'"';
        }

        return $value;
    }
}
