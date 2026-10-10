<?php

namespace App\Providers;

use App\Models\Usuario;
use App\Support\EnvEditor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->garantirEnvBasico();
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Em hospedagens compartilhadas o app costuma ficar atrás de proxy HTTPS.
        if (str_starts_with((string) Config::get('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Model::preventSilentlyDiscardingAttributes(false);

        $this->registrarPermissoes();

        View::share('barbearia', Config::get('barbearia'));
        View::share('barbeariaNome', Config::get('barbearia.nome'));

        $this->registrarDiretivasBlade();
    }

    /**
     * Atalhos usados nas telas: @money(10), @data($model->data), @dataHora($model->created_at)
     */
    protected function registrarDiretivasBlade(): void
    {
        Blade::directive('money', fn ($expressao) => "<?php echo 'R$ '.number_format((float) ($expressao), 2, ',', '.'); ?>");
        Blade::directive('data', fn ($expressao) => "<?php echo ($expressao) ? \Illuminate\Support\Carbon::parse($expressao)->format('d/m/Y') : '-'; ?>");
        Blade::directive('dataHora', fn ($expressao) => "<?php echo ($expressao) ? \Illuminate\Support\Carbon::parse($expressao)->format('d/m/Y H:i') : '-'; ?>");
        Blade::directive('diaSemana', fn ($expressao) => "<?php echo ($expressao) ? ucfirst(\Illuminate\Support\Carbon::parse($expressao)->locale('pt_BR')->dayName) : '-'; ?>");
    }

    /**
     * Se o .env não existir (instalação recém enviada para a hospedagem), cria a partir
     * do .env.example e gera a APP_KEY. Assim nenhuma tela quebra por falta de chave.
     */
    protected function garantirEnvBasico(): void
    {
        try {
            $key = EnvEditor::ensureAppKey();

            if ($key && empty(Config::get('app.key'))) {
                Config::set('app.key', $key);
            }
        } catch (\Throwable $e) {
            // Sem .env gravável (permissão) ainda assim mantém o sistema no ar nesta requisição.
            if (empty(Config::get('app.key'))) {
                Config::set('app.key', EnvEditor::generateKey());
            }
        }
    }

    /**
     * Permissões simples baseadas no papel do usuário (sem pacotes externos).
     */
    protected function registrarPermissoes(): void
    {
        Gate::before(function ($usuario, $ability) {
            return $usuario instanceof Usuario && $usuario->isAdmin() ? true : null;
        });

        foreach ((array) Config::get('barbearia.permissoes', []) as $habilidade => $papeis) {
            Gate::define($habilidade, function ($usuario) use ($papeis) {
                return $usuario instanceof Usuario && $usuario->hasAnyRole((array) $papeis);
            });
        }

        Gate::define('acesso-painel', function ($usuario) {
            return $usuario instanceof Usuario && $usuario->ativo && $usuario->role !== Usuario::ROLE_CLIENTE;
        });
    }
}
