# Documentação de Fluxo — inServices

Mapeamento das classes do domínio (`app/`) e de como elas se conectam, do ponto de entrada HTTP até o resultado final. Nomes de classes, métodos e variáveis seguem exatamente o código-fonte.

## Enums

### StatusAgendamento
Valores possíveis do ciclo de vida de um agendamento: `Pendente`, `Confirmado`, `Concluido`, `CanceladoPeloPrestador`, `CanceladoPeloCliente`, `NaoCompareceu`. Usado por `AgendamentoPublicoController` para marcar o status inicial (`Pendente`) e filtrar cancelamentos.

### StatusAssinatura
Valores do ciclo de vida de uma assinatura: `Teste`, `Ativa`, `Pendente`, `Vencida`, `Suspensa`, `Cancelada`. Consumido por `ServicoAcessoPrestador` para decidir liberação de acesso.

### StatusMensalidade
Valores do ciclo de vida de uma mensalidade: `Pendente`, `Paga`, `Vencida`, `Cancelada`, `Isenta`, `EmAnalise`. Também consumido por `ServicoAcessoPrestador`.

## Support

### Moeda
- **Responsabilidade**: converter valores monetários entre representação de formulário (`1.234,56` ou number) e decimal interno (`1234.56`), e formatar decimal para exibição em real (`R$ 1.234,56`).
- **Principais métodos**:
  - `paraDecimal(mixed $valor): ?string` — normaliza entrada de formulário (string com vírgula/ponto ou número) para decimal com ponto, `null` se vazio/inválido.
  - `paraReal(mixed $valor): string` — formata decimal para string `1.234,56`.
- **Dependências**: usada por `SalvarPlanoRequest` e `SalvarServicoRequest` em `prepareForValidation()`, antes das regras de validação rodarem.
- **Fluxo de execução**: entra em ação sempre que um formulário de plano ou serviço é submetido, convertendo o campo de valor antes da validação numérica.

## Models (`app/Models`)

### User
- **Responsabilidade**: usuário autenticável do sistema (`Authenticatable`), com `tipo` (`administrador_geral` ou `prestador`) definindo o papel.
- **Relacionamentos**: `perfilPrestador(): HasOne` para `PerfilPrestador`.
- **Fluxo de execução**: criado em `CadastroPrestadorController::store` (tipo `prestador`) ou via seeders (`administrador_geral`); autenticado em `SessaoController`.

### PerfilPrestador
- **Responsabilidade**: perfil público e operacional do prestador (nome, slug para URL pública, status de conta).
- **Relacionamentos**: `usuario` (BelongsTo `User`), `assinatura` (HasOne `Assinatura`), `profissionais`, `servicos`, `clientes`, `agendamentos` (HasMany).
- **Fluxo de execução**: é o "tenant" de isolamento — praticamente todos os controllers de `Prestador\*` resolvem `auth()->user()->perfilPrestador()` e filtram tudo por `prestador_id`. Também é o parâmetro de rota (`{prestador:uuid_publico}`) da área pública de agendamento.

### Plano
- **Responsabilidade**: define limites e preço de um plano de assinatura (quantidade máxima de serviços, profissionais, agendamentos/mês, tolerância de atraso em dias).
- **Relacionamentos**: `assinaturas(): HasMany`.
- **Fluxo de execução**: gerenciado por `Admin\PlanosController`; consultado por `ServicosController`, `ProfissionaisController` e `AssinaturaController` para validar limites antes de criar recursos.

### Assinatura
- **Responsabilidade**: vincula um `PerfilPrestador` a um `Plano`, controlando status, datas de vencimento, período de teste gratuito e liberação manual de acesso.
- **Relacionamentos**: `prestador` (BelongsTo), `plano` (BelongsTo), `mensalidades` (HasMany).
- **Fluxo de execução**: criada no cadastro público (`CadastroPrestadorController::store`) com status `teste`; lida por `ServicoAcessoPrestador::verificar` para decidir se o prestador pode operar.

### Mensalidade
- **Responsabilidade**: cobrança mensal derivada de uma assinatura, com valores original/desconto/acréscimo/final e datas de emissão, vencimento e pagamento.
- **Relacionamentos**: `prestador` (BelongsTo), `assinatura` (BelongsTo), `pagamentos` (HasMany).
- **Fluxo de execução**: primeira mensalidade criada junto com a assinatura no cadastro público; listada em `Admin\MensalidadesController` e `Prestador\MensalidadesController`; seu status/vencimento é a base da decisão de acesso em `ServicoAcessoPrestador`.

### Pagamento
- **Responsabilidade**: registro de um pagamento recebido para quitar uma mensalidade.
- **Relacionamentos**: `mensalidade` (BelongsTo).
- **Fluxo de execução**: modelado no domínio, mas ainda sem controller/fluxo de escrita nesta etapa (ver seção "O que ainda não está fechado").

### Profissional
- **Responsabilidade**: pessoa que presta o serviço dentro de um `PerfilPrestador`, com agenda própria.
- **Relacionamentos**: `prestador` (BelongsTo), `servicos` (BelongsToMany, pivot `profissional_servico` com `duracao_minutos`, `preco`, `intervalo_adicional_minutos`, `ativo`, `ordem_exibicao`), `regrasDisponibilidade`/`excecoesDisponibilidade`/`agendamentos` (HasMany).
- **Fluxo de execução**: CRUD em `Prestador\ProfissionaisController`, respeitando `quantidade_maxima_profissionais` do plano; consumido por `CalcularHorariosDisponiveis` e `AtribuirProfissionalDisponivel` para montar a agenda.

### Servico
- **Responsabilidade**: serviço oferecido por um prestador, com duração, preço, regras de antecedência mínima e limite de dias futuros para agendamento.
- **Relacionamentos**: `prestador` (BelongsTo), `profissionais` (BelongsToMany, mesmo pivot acima).
- **Fluxo de execução**: CRUD em `Prestador\ServicosController`, respeitando `quantidade_maxima_servicos` do plano; é o ponto de partida do cálculo de horários (`CalcularHorariosDisponiveis::executar`).

### RegraDisponibilidade
- **Responsabilidade**: janela de horário recorrente por dia da semana (`dia_semana` 0–6) de um profissional, com intervalo de almoço opcional.
- **Relacionamentos**: `profissional` (BelongsTo).
- **Fluxo de execução**: criada em `Prestador\AgendaController::storeRegra` (uma por dia da semana selecionado); consumida por `CalcularHorariosDisponiveis::horariosDoProfissional` como a janela base de onde os slots são gerados.

### ExcecaoDisponibilidade
- **Responsabilidade**: bloqueio ou ajuste pontual de disponibilidade em uma data específica (ex.: folga, feriado).
- **Relacionamentos**: `profissional` (BelongsTo).
- **Fluxo de execução**: consumida por `CalcularHorariosDisponiveis::periodoLivre` para remover horários da data bloqueada; ainda sem tela de cadastro nesta etapa (ver seção final).

### Cliente
- **Responsabilidade**: cliente final de um prestador, identificado publicamente por telefone normalizado (sem exigir login).
- **Relacionamentos**: `prestador` (BelongsTo), `agendamentos`/`dispositivos` (HasMany).
- **Fluxo de execução**: criado/atualizado via `AgendamentoPublicoController::identificar` (`Cliente::updateOrCreate` por `prestador_id` + `telefone_normalizado`); listado e editado por `Prestador\ClientesController`.

### DispositivoCliente
- **Responsabilidade**: token persistente de reconhecimento de dispositivo do cliente (planejado; guarda apenas `token_hash`, nunca o token original).
- **Relacionamentos**: `cliente` (BelongsTo).
- **Fluxo de execução**: modelado no domínio, mas o fluxo de emissão/validação do cookie `HttpOnly` ainda não está implementado nos controllers atuais — a identificação hoje usa apenas `session()`.

### Agendamento
- **Responsabilidade**: reserva de horário de um cliente com um profissional para um serviço, com protocolo público e chave de idempotência.
- **Relacionamentos**: `prestador`, `cliente`, `servico`, `profissional` (BelongsTo), `historicos` (HasMany).
- **Fluxo de execução**: criado em `AgendamentoPublicoController::confirmar` via `Agendamento::firstOrCreate` usando `chave_idempotencia` (hash de cliente+serviço+profissional+início) para evitar duplicidade; lido pelo painel do prestador (`PainelPrestadorController`) e pela agenda.

### HistoricoStatusAgendamento
- **Responsabilidade**: trilha de auditoria de mudanças de status de um agendamento.
- **Relacionamentos**: `agendamento` (BelongsTo).
- **Fluxo de execução**: uma entrada é criada em `AgendamentoPublicoController::confirmar` apenas quando o agendamento é efetivamente novo (`$agendamento->wasRecentlyCreated`).

### Notificacao
- **Responsabilidade**: notificação in-app para um destinatário polimórfico (`destinatario_type`/`destinatario_id`), com corpo, URL de destino e payload livre em `dados` (JSON).
- **Fluxo de execução**: criada em `AgendamentoPublicoController::confirmar` para avisar o prestador de um novo agendamento; listada e marcada como lida por `NotificacoesController` (endpoint AJAX consumido pelo layout).

### InscricaoPush
- **Responsabilidade**: assinatura Web Push de um destinatário polimórfico (endpoint, chaves e codificação do navegador).
- **Fluxo de execução**: modelada para a etapa futura de Web Push (ver `README.md`); ainda não há controller que a popule ou consuma.

### ModeloMensagemWhatsapp
- **Responsabilidade**: texto complementar configurável pelo prestador para mensagens de WhatsApp (ex.: modelo "contato").
- **Fluxo de execução**: obtido/criado sob demanda (`firstOrCreate`) em `PainelPrestadorController` e `Prestador\MensagensWhatsappController`; seu campo `mensagem` é combinado com um texto padrão por `ServicoWhatsapp::preencherModelo` ao montar o link de confirmação exibido no painel.

## Services (`app/Services`)

### ServicoTelefone
- **Responsabilidade**: normalizar telefones brasileiros para o formato usado em links de WhatsApp e validar se um telefone normalizado é utilizável.
- **Principais métodos**:
  - `normalizarBrasil(?string $telefone): ?string` — remove tudo que não é dígito; se já começa com `55` mantém, se tem 10–11 dígitos prefixa `55`, senão devolve os dígitos como estão.
  - `telefoneValidoParaWhatsapp(?string $telefone): bool` — normaliza e checa se o resultado tem entre 12 e 14 dígitos.
- **Dependências**: usada por `ServicoWhatsapp`, e diretamente pelos controllers que salvam telefone (`CadastroPrestadorController`, `ClientesController`, `ProfissionaisController`, `AgendamentoPublicoController::identificar`).
- **Fluxo de execução**: entra em ação sempre que um telefone é persistido, para preencher a coluna `telefone_normalizado` usada depois em buscas e geração de link.

### ServicoWhatsapp
- **Responsabilidade**: gerar o link `https://wa.me/...` de contato e preencher templates de mensagem com placeholders `{chave}`.
- **Principais métodos**:
  - `gerarLink(string $telefone, string $mensagem): string` — normaliza o telefone via `ServicoTelefone`, lança `InvalidArgumentException` se inválido, senão monta a URL com a mensagem codificada (`rawurlencode`).
  - `preencherModelo(string $modelo, array $dados): string` — substitui cada `{chave}` do modelo pelo valor correspondente em `$dados`.
- **Dependências**: injeta `ServicoTelefone` no construtor.
- **Fluxo de execução**: usado em `PainelPrestadorController::dadosPainel` para montar, para cada próximo agendamento, a mensagem de confirmação (texto padrão + complemento do `ModeloMensagemWhatsapp`) e o link `wa.me` correspondente. Não existe chat interno — a comunicação é sempre via link externo para o WhatsApp do cliente.

### CalcularHorariosDisponiveis
- **Responsabilidade**: calcular os horários livres de um serviço em uma data, por profissional, respeitando regras de disponibilidade, exceções, almoço e agendamentos já ocupados.
- **Principais métodos**:
  - `executar(Servico $servico, CarbonImmutable $data, ?Profissional $profissional = null): Collection` — resolve a lista de profissionais aptos (todos vinculados e ativos, ou um específico) e concatena os horários de cada um, ordenados por início e profissional.
  - `horariosDoProfissional` (privado) — para um profissional, monta os slots percorrendo a janela de `RegraDisponibilidade` do dia da semana em passos de `duracao + intervalo` (mínimo 15 min), pulando os que colidem com `ExcecaoDisponibilidade`, agendamentos ocupados ou o intervalo de almoço.
  - `periodoLivre` / `foraDoAlmoco` (privados) — checagens de sobreposição de intervalo.
- **Dependências**: consome `Servico`, `Profissional`, `RegraDisponibilidade`, `ExcecaoDisponibilidade`, `Agendamento`.
- **Fluxo de execução**: chamado por `AgendamentoPublicoController::horarios` (lista horários para o cliente escolher) e `::confirmar` (revalida que o horário escolhido ainda está livre antes de gravar); também usado por `AtribuirProfissionalDisponivel`.
- **Regra não óbvia**: um horário ocupado de um profissional nunca bloqueia outro — a checagem de conflito é sempre por profissional, permitindo agenda simultânea entre a equipe.

### ServicoAcessoPrestador
- **Responsabilidade**: decidir se um prestador pode operar a agenda pública, com base no status da conta, da assinatura e da mensalidade.
- **Principais métodos**:
  - `verificar(PerfilPrestador $prestador): array` — retorna `['permitido' => bool, 'alerta' => ?string]` seguindo, em ordem: conta bloqueada/cancelada → nega; sem assinatura ou plano inativo → nega; `acesso_liberado_ate` vigente → libera (override manual); `periodo_gratuito_ate` vigente → libera sem alerta; assinatura `cancelada` → nega; sem mensalidade vencida pendente → libera se status é `ativa`/`teste`; mensalidade vencida mas dentro da tolerância em dias úteis do plano → libera com alerta; fora da tolerância → nega.
  - `adicionarDiasUteis` (privado) — soma dias úteis (pula sábado/domingo) a partir de uma data.
- **Dependências**: `PerfilPrestador`, `Assinatura`, `Mensalidade`, enums `StatusAssinatura`/`StatusMensalidade`.
- **Fluxo de execução**: chamado pela `Action` `VerificarAcessoPrestador`, que por sua vez é injetada em todas as ações de `AgendamentoPublicoController` para bloquear a agenda pública quando o prestador está inadimplente fora da tolerância.
- **Regra não óbvia**: a ordem das checagens importa — liberação manual e período de teste grátis sempre têm prioridade sobre inadimplência, mesmo que a mensalidade já esteja vencida.

## Actions (`app/Actions`)

### VerificarAcessoPrestador
- **Responsabilidade**: fachada fina de caso de uso sobre `ServicoAcessoPrestador::verificar`, para ser injetada diretamente nos controllers via resolução automática de dependências do Laravel.
- **Fluxo de execução**: usado em todas as rotas de `Publico\AgendamentoPublicoController` (index, servico, horarios, confirmar) como guarda de acesso antes de expor a agenda ou aceitar uma confirmação.

### AtribuirProfissionalDisponivel
- **Responsabilidade**: escolher automaticamente, entre os profissionais disponíveis para um serviço em um horário, o que tem menor número de agendamentos no mês (distribuição de carga).
- **Principais métodos**: `executar(Servico $servico, CarbonImmutable $inicio): ?Profissional` — usa `CalcularHorariosDisponiveis` para obter os profissionais livres naquele horário exato, depois ordena por contagem de agendamentos do mês (`withCount`) e retorna o primeiro (`null` se ninguém está livre).
- **Dependências**: injeta `CalcularHorariosDisponiveis`.
- **Fluxo de execução**: implementada e testável isoladamente, mas **ainda não é chamada por nenhum controller** — hoje `AgendamentoPublicoController::confirmar` exige que o cliente escolha explicitamente o `profissional_id`. É a peça pronta para uma futura opção "qualquer profissional disponível" no formulário público.

## Controllers (`app/Http/Controllers`)

Controllers mantêm apenas orquestração HTTP: resolvem o prestador autenticado (ou por rota pública), delegam regra de negócio a Services/Actions e devolvem `View`/`RedirectResponse`/`JsonResponse`.

- **Auth\SessaoController**: login (`create`/`store` com `EntrarRequest` + `Auth::attempt`) e logout (`destroy`), com redirecionamento por `tipo` de usuário.
- **Auth\CadastroPrestadorController**: `create` exibe o formulário de planos ativos; `store` roda uma transação (`DB::transaction`) que cria `User` (tipo `prestador`), gera `slug` único para `PerfilPrestador`, cria a `Assinatura` em status `teste` com 15 dias grátis (`DIAS_TESTE_GRATUITO`) e a primeira `Mensalidade` pendente, depois autentica o usuário.
- **ContaController**: edição de dados da conta logada e troca de senha (`AlterarSenhaRequest`).
- **NotificacoesController**: endpoint JSON consumido via AJAX pelo layout para listar notificações não lidas do usuário logado e marcá-las como lidas.
- **Admin\PainelAdminController**: dashboard do administrador geral (contagens de prestadores por status, mensalidades vencidas, receita do mês, lista de planos e prestadores).
- **Admin\PlanosController**: CRUD de `Plano` (`SalvarPlanoRequest`); `destroy` na verdade alterna `ativo`, e é bloqueado se o plano já tem assinaturas vinculadas.
- **Admin\PrestadoresController**: lista prestadores e alterna status `ativo`/`suspenso`.
- **Admin\MensalidadesController**: lista todas as mensalidades do sistema com prestador e plano carregados.
- **Prestador\PainelPrestadorController**: dashboard do prestador — `__invoke` renderiza a view completa; `dados()` expõe as mesmas métricas em JSON para atualização assíncrona. `resumoDashboard` (privado) concentra as queries de métricas do mês/dia; `dadosPainel` (privado) monta a lista de próximos agendamentos já com o link do WhatsApp pronto (via `ServicoWhatsapp`).
- **Prestador\ServicosController**: CRUD de `Servico` (`SalvarServicoRequest`), valida `quantidade_maxima_servicos` do plano antes de criar, sincroniza profissionais vinculados (`idsProfissionaisDoPrestador` valida que os IDs pertencem ao próprio prestador) e troca a imagem no storage público quando enviada.
- **Prestador\ProfissionaisController**: CRUD de `Profissional` (`SalvarProfissionalRequest`), valida `quantidade_maxima_profissionais` do plano ao criar ou reativar (`alternarStatus`), impede exclusão de profissional com agendamentos futuros.
- **Prestador\AgendaController**: lista regras de disponibilidade dos profissionais ativos do prestador; `storeRegra` cria uma `RegraDisponibilidade` por dia da semana marcado no formulário; `destroyRegra` confere que a regra pertence a um profissional do prestador logado antes de excluir.
- **Prestador\ClientesController**: lista e edita `Cliente` do prestador, sempre validando `prestador_id` antes de qualquer leitura/escrita.
- **Prestador\AssinaturaController**: exibe e troca o plano da assinatura do prestador, validando que os totais atuais de serviços/profissionais ativos cabem no novo plano antes de trocar.
- **Prestador\MensalidadesController**: lista mensalidades do prestador e identifica a mensalidade em aberto (`pendente`/`vencida`) mais próxima do vencimento.
- **Prestador\MensagensWhatsappController**: edita o `ModeloMensagemWhatsapp` do tipo "contato" do prestador (texto complementar usado por `ServicoWhatsapp`).
- **Publico\AgendamentoPublicoController**: fluxo público completo de agendamento — ver seção seguinte.

## Http\Requests (`app/Http/Requests`)

Validam apenas a forma dos dados de entrada; regras de negócio (limites de plano, unicidade de telefone etc.) ficam nos controllers/services.

- **EntrarRequest**: `email` + `senha` obrigatórios para login.
- **CadastrarPrestadorRequest**: dados completos do cadastro público (plano, responsável, nome público, e-mail único, telefone, senha confirmada, aceite de privacidade obrigatório).
- **AlterarSenhaRequest**: nova senha confirmada, exige usuário autenticado.
- **AtualizarClienteRequest**: nome/telefone obrigatórios, e-mail opcional, restrito a `tipo === 'prestador'`.
- **IdentificarClientePublicoRequest**: nome/telefone obrigatórios na tela pública de identificação do cliente.
- **SalvarPlanoRequest**: normaliza `valor_mensal` via `Moeda::paraDecimal` antes de validar; restrito a `tipo === 'administrador_geral'`.
- **SalvarServicoRequest**: normaliza `preco` (via `Moeda`) e gera `slug` (via `Str::slug`) antes de validar; restrito a `tipo === 'prestador'`.
- **SalvarProfissionalRequest**: nome obrigatório, demais campos opcionais; restrito a `tipo === 'prestador'`.
- **SalvarRegraDisponibilidadeRequest**: valida array de `dias_semana`, janelas `H:i` consistentes (`horario_fim` após `horario_inicio`, almoço dentro da janela); restrito a `tipo === 'prestador'`.

## Fluxo geral do sistema

### 1. Cadastro de um novo prestador
`GET /cadastro` (`CadastroPrestadorController::create`) exibe os planos ativos → `POST /cadastro` (`store`, validado por `CadastrarPrestadorRequest`) roda em transação: cria `User`, gera `slug` único, cria `PerfilPrestador` em status `teste`, cria `Assinatura` com `periodo_gratuito_ate` = hoje + 15 dias, cria a primeira `Mensalidade` pendente → autentica o usuário e redireciona para `/painel`.

### 2. Login e roteamento por tipo de usuário
`POST /login` (`SessaoController::store`, validado por `EntrarRequest`) autentica via `Auth::attempt` → redireciona para `/admin` (se `tipo === 'administrador_geral'`) ou `/painel` (demais casos). A rota raiz `/` aplica a mesma regra para usuários já logados.

### 3. Painel do prestador
`GET /painel` (`PainelPrestadorController::__invoke`) monta métricas do mês/dia (`resumoDashboard`) e a lista de próximos agendamentos já com link de WhatsApp pronto (`ServicoWhatsapp::gerarLink` sobre o telefone normalizado do cliente). `GET /painel/dados` expõe o mesmo resumo em JSON para o front atualizar sem recarregar a página.

### 4. Agendamento público (fluxo principal do produto)
1. Cliente acessa `GET /agendar/{prestador:uuid_publico}` (`AgendamentoPublicoController::index`). Se ainda não identificado nesta sessão, vê o formulário de identificação; `acesso` já é calculado via `VerificarAcessoPrestador` para eventualmente bloquear a agenda.
2. `POST /agendar/{prestador}/identificar` (`identificar`, validado por `IdentificarClientePublicoRequest`) normaliza o telefone (`ServicoTelefone`) e cria/atualiza o `Cliente` (`updateOrCreate` por `prestador_id` + `telefone_normalizado`), guardando o `cliente_id` na sessão sob a chave `cliente_publico_{prestador_id}`.
3. `GET /agendar/{prestador}/{servico:uuid_publico}` (`servico`) exige cliente identificado; carrega os profissionais do serviço.
4. `GET /agendar/{prestador}/{servico}/horarios` (`horarios`, AJAX) reexecuta `VerificarAcessoPrestador`; se permitido, devolve os slots calculados por `CalcularHorariosDisponiveis::executar` para a data pedida.
5. `POST /agendar/{prestador}/{servico}/confirmar` (`confirmar`) revalida acesso, revalida que o slot escolhido ainda está livre (recalcula com `CalcularHorariosDisponiveis` e confere se o horário exato ainda consta), monta uma `chave_idempotencia` (hash de cliente+serviço+profissional+início) e cria o `Agendamento` via `firstOrCreate` — reenvios do mesmo formulário nunca duplicam a reserva. Se o registro é novo, grava `HistoricoStatusAgendamento` e dispara uma `Notificacao` in-app para o dono do prestador.
6. O prestador vê o novo agendamento no painel (passo 3) e usa o link de WhatsApp gerado para confirmar manualmente com o cliente — não há chat nem notificação push real nesta etapa.

### 5. Controle de acesso por inadimplência
Em todo ponto de entrada da agenda pública (`index`, `servico`, `horarios`, `confirmar`), o controller chama `VerificarAcessoPrestador::executar`, que delega a `ServicoAcessoPrestador::verificar`. Essa é a única porta de decisão sobre bloquear ou não a agenda: conta suspensa/cancelada bloqueia sempre; liberação manual e teste grátis sempre liberam; fora disso, o status da assinatura e o vencimento da mensalidade (com tolerância em dias úteis definida no `Plano`) decidem.

### 6. Administração geral (planos, prestadores, mensalidades)
O administrador (`tipo === 'administrador_geral'`) gerencia `Plano` (CRUD com inativação em vez de exclusão física quando há assinaturas vinculadas), visualiza todos os `PerfilPrestador` e pode alternar seu status (`ativo`/`suspenso`), e acompanha todas as `Mensalidade` do sistema. Nenhuma dessas telas altera diretamente uma agenda de prestador — o efeito é sempre indireto, via `ServicoAcessoPrestador` na próxima requisição pública daquele prestador.

## O que ainda não está fechado (visível no código atual)

- `Pagamento` está modelado e relacionado a `Mensalidade`, mas não há controller/rota que crie ou liste pagamentos.
- `ExcecaoDisponibilidade` é lida pelo cálculo de horários, mas não há tela de cadastro para o prestador criar bloqueios pontuais.
- `DispositivoCliente` e `InscricaoPush` estão modelados para reconhecimento persistente de cliente e Web Push, mas nenhum controller os popula ainda; a identificação do cliente hoje depende só de `session()`.
- `AtribuirProfissionalDisponivel` existe e é testável, mas não é chamada por `AgendamentoPublicoController::confirmar`, que exige o `profissional_id` explícito do cliente.

Essas lacunas batem com a seção "Proximas etapas" do `README.md` e não são bugs — são escopo ainda não implementado.
