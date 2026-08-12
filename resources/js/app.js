import './bootstrap';

let instaladorPwa = null;

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    instaladorPwa = event;
});

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js');
    });
}

$(function () {
    let formularioBoxalert = null;
    const emModoApp = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

    if (emModoApp) {
        $('[data-instalar-pwa]').hide();
    }

    const mostrarNotificacaoLocal = async function (titulo, corpo, url) {
        if (! titulo || ! ('Notification' in window) || ! ('serviceWorker' in navigator) || Notification.permission !== 'granted') {
            return;
        }

        const registro = await navigator.serviceWorker.ready;
        registro.showNotification(titulo, {
            body: corpo || 'Abra o inServices para ver os detalhes.',
            data: { url: url || window.location.href },
            icon: '/icons/icon-192.png',
            badge: '/icons/icon-192.png'
        });
    };

    const atualizarAvisoNotificacoes = function () {
        const $banner = $('[data-notification-permission]');

        if (! $banner.length || ! ('Notification' in window) || ! ('serviceWorker' in navigator)) {
            $banner.attr('hidden', true);
            return;
        }

        if (Notification.permission === 'default') {
            $banner.removeAttr('hidden');
            return;
        }

        $banner.attr('hidden', true);
    };

    atualizarAvisoNotificacoes();

    $('[data-ativar-notificacoes]').on('click', async function () {
        if (! ('Notification' in window)) {
            return;
        }

        await Notification.requestPermission();
        atualizarAvisoNotificacoes();
    });

    $('[data-notification-toggle]').on('click', function (event) {
        event.stopPropagation();
        $('[data-notification-dropdown]').prop('hidden', ! $('[data-notification-dropdown]').prop('hidden'));
    });

    $(document).on('click', function (event) {
        if (! $(event.target).closest('[data-notification-menu]').length) {
            $('[data-notification-dropdown]').prop('hidden', true);
        }
    });

    const escaparHtml = function (valor) {
        return $('<div>').text(valor || '').html();
    };

    const renderizarNotificacoes = function (notificacoes) {
        const $lista = $('[data-notification-list]');

        if (! $lista.length) {
            return;
        }

        if (! notificacoes.length) {
            $lista.html('<div class="notification-empty">Nenhuma notificacao por enquanto.</div>');
            return;
        }

        $lista.html(notificacoes.map(function (notificacao) {
            const classe = notificacao.lida ? '' : ' unread';
            const url = notificacao.url || '#';

            return `
                <a class="notification-item${classe}" href="${escaparHtml(url)}">
                    <strong>${escaparHtml(notificacao.titulo)}</strong>
                    <span>${escaparHtml(notificacao.corpo)}</span>
                    <small>${escaparHtml(notificacao.criada_em)}</small>
                </a>
            `;
        }).join(''));
    };

    const atualizarNotificacoesTopo = function (dados, avisarNovidade = false) {
        const $menu = $('[data-notification-menu]');
        const $contador = $('[data-notification-count]');
        const $formLidas = $('[data-notification-read-form]');
        const ultimaIdAtual = Number($menu.data('notification-latest-id')) || 0;
        const novaUltimaId = Number(dados.ultima_id) || 0;

        $contador.text(dados.nao_lidas || 0).prop('hidden', ! dados.nao_lidas);
        $formLidas.prop('hidden', ! dados.nao_lidas);
        renderizarNotificacoes(dados.notificacoes || []);

        if (avisarNovidade && novaUltimaId > ultimaIdAtual) {
            const nova = (dados.notificacoes || []).find((notificacao) => Number(notificacao.id) === novaUltimaId);
            mostrarNotificacaoLocal(nova?.titulo, nova?.corpo, nova?.url);
        }

        $menu.data('notification-latest-id', novaUltimaId);
    };

    const buscarNotificacoesTopo = function (avisarNovidade = true) {
        const url = $('[data-notification-menu]').data('notifications-url');

        if (! url) {
            return;
        }

        $.getJSON(url).done(function (dados) {
            atualizarNotificacoesTopo(dados, avisarNovidade);
        });
    };

    if ($('[data-notification-menu]').length) {
        setInterval(function () {
            buscarNotificacoesTopo(true);
        }, 2000);
    }

    const atualizarDadosHome = function () {
        const $dashboard = $('[data-home-dashboard]');
        const url = $dashboard.data('home-dashboard-url');

        if (! url) {
            return;
        }

        $.getJSON(url).done(function (dados) {
            Object.entries(dados.metricas || {}).forEach(function ([chave, valor]) {
                $(`[data-home-metric="${chave}"]`).text(valor);
            });

            $('[data-home-bar="percentual_recebido_mes"]').css('width', `${dados.metricas?.percentual_recebido_mes || 0}%`);
            $('[data-home-bar-fixed="atendimentos_mes"]').css('width', `${Math.min(100, (Number(dados.metricas?.atendimentos_mes) || 0) * 10)}%`);

            const ticketNumerico = (dados.metricas?.ticket_medio_mes || '0').toString().replace(/\D/g, '');
            $('[data-home-bar-money="ticket_medio_mes"]').css('width', `${Math.min(100, (Number(ticketNumerico) || 0) / 100)}%`);

            const agendaHoje = dados.agenda_hoje || [];
            $('[data-home-agenda-hoje]').html(agendaHoje.length ? agendaHoje.map(function (profissional) {
                const itens = profissional.agendamentos.length ? profissional.agendamentos.map(function (agendamento) {
                    return `
                        <div class="agenda-dia-item">
                            <time>${escaparHtml(agendamento.horario)}</time>
                            <span>${escaparHtml(agendamento.cliente)}</span>
                            <small>${escaparHtml(agendamento.servico)}</small>
                        </div>
                    `;
                }).join('') : '<p class="empty-line">Sem agendamentos hoje.</p>';

                return `
                    <article class="agenda-profissional-card">
                        <div>
                            <strong>${escaparHtml(profissional.nome)}</strong>
                            <span>${profissional.total} hoje</span>
                        </div>
                        ${itens}
                    </article>
                `;
            }).join('') : '<div class="empty-state">Nenhum profissional ativo cadastrado.</div>');

            const resumoProfissionais = dados.resumo_profissionais || [];
            $('[data-home-resumo-profissionais]').html(resumoProfissionais.length ? resumoProfissionais.map(function (profissional) {
                return `
                    <article class="professional-summary-item">
                        <div>
                            <strong>${escaparHtml(profissional.nome)}</strong>
                            <span>${profissional.total_mes} atendimentos</span>
                        </div>
                        <b>${escaparHtml(profissional.recebido_mes)}</b>
                    </article>
                `;
            }).join('') : '<div class="empty-state">Nenhum profissional ativo cadastrado.</div>');
        });
    };

    if ($('[data-home-dashboard]').length) {
        setInterval(atualizarDadosHome, 2000);
    }

    $('[data-bs-toggle="tooltip"]').each(function () {
        new bootstrap.Tooltip(this);
    });

    $('[data-multiselect-tags]').each(function () {
        const $select = $(this);
        const placeholder = $select.data('placeholder') || 'Selecione';
        const $campo = $('<div class="multi-tags-field" tabindex="0" role="combobox" aria-expanded="false"></div>');
        const $tags = $('<div class="multi-tags-selected"></div>');
        const $placeholder = $(`<span class="multi-tags-placeholder">${placeholder}</span>`);
        const $lista = $('<div class="multi-tags-list" role="listbox"></div>');

        $select.find('option').each(function () {
            const $option = $(this);
            const $item = $('<button class="multi-tags-option" type="button" role="option"></button>')
                .text($option.text())
                .attr('data-value', $option.val());

            $lista.append($item);
        });

        $tags.append($placeholder);
        $campo.append($tags, $lista);
        $select.after($campo).addClass('visually-hidden-select');

        const atualizar = function () {
            const selecionados = $select.find('option:selected');
            $tags.empty();

            if (! selecionados.length) {
                $tags.append($placeholder);
            }

            selecionados.each(function () {
                const $option = $(this);
                const $tag = $('<span class="multi-tag"></span>').text($option.text());
                const $remover = $('<button type="button" aria-label="Remover dia">x</button>').on('click', function (event) {
                    event.stopPropagation();
                    $option.prop('selected', false);
                    $select.trigger('change');
                    atualizar();
                });

                $tag.prepend($remover);
                $tags.append($tag);
            });

            $lista.find('.multi-tags-option').each(function () {
                const selecionado = $select.find(`option[value="${$(this).data('value')}"]`).prop('selected');
                $(this).toggleClass('active', selecionado).attr('aria-selected', selecionado ? 'true' : 'false');
            });
        };

        $campo.on('click', function () {
            $campo.addClass('open').attr('aria-expanded', 'true');
        });

        $campo.on('keydown', function (event) {
            if (event.key === 'Escape') {
                $campo.removeClass('open').attr('aria-expanded', 'false');
            }
        });

        $lista.on('click', '.multi-tags-option', function (event) {
            event.stopPropagation();
            const valor = $(this).data('value').toString();
            const $option = $select.find(`option[value="${valor}"]`);

            $option.prop('selected', ! $option.prop('selected'));
            $select.trigger('change');
            atualizar();
        });

        $(document).on('click', function (event) {
            if (! $.contains($campo[0], event.target)) {
                $campo.removeClass('open').attr('aria-expanded', 'false');
            }
        });

        atualizar();
    });

    const moedaParaCentavos = function (valor) {
        const apenasNumeros = (valor || '').toString().replace(/\D/g, '');
        return parseInt(apenasNumeros || '0', 10);
    };

    const formatarMoeda = function (valor) {
        const centavos = moedaParaCentavos(valor);
        return (centavos / 100).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    };

    const moedaParaDecimal = function (valor) {
        const centavos = moedaParaCentavos(valor);
        return (centavos / 100).toFixed(2);
    };

    $('[data-mascara-moeda]').each(function () {
        $(this).val(formatarMoeda($(this).val()));
    });

    $('[data-mascara-moeda]').on('input', function () {
        $(this).val(formatarMoeda($(this).val()));
    });

    $('[data-mascara-moeda]').closest('form').on('submit', function () {
        $(this).find('[data-mascara-moeda]').each(function () {
            $(this).val(moedaParaDecimal($(this).val()));
        });
    });

    $('[data-alternar-menu]').on('click', function () {
        $('body').toggleClass('menu-aberto');
        $(this).attr('aria-expanded', $('body').hasClass('menu-aberto') ? 'true' : 'false');
    });

    $('[data-fechar-menu], .app-sidebar .nav-link').on('click', function () {
        $('body').removeClass('menu-aberto');
        $('[data-alternar-menu]').attr('aria-expanded', 'false');
    });

    $(document).on('keydown', function (event) {
        if (event.key === 'Escape') {
            $('body').removeClass('menu-aberto');
            $('[data-alternar-menu]').attr('aria-expanded', 'false');
            fecharBoxalert();
        }
    });

    const abrirBoxalert = function (mensagem, formulario) {
        formularioBoxalert = formulario;
        $('[data-boxalert-mensagem]').text(mensagem || 'Deseja continuar?');
        $('[data-boxalert-backdrop]').removeAttr('hidden');
        $('[data-boxalert-confirmar]').trigger('focus');
    };

    const fecharBoxalert = function () {
        $('[data-boxalert-backdrop]').attr('hidden', true);
        formularioBoxalert = null;
    };

    $('[data-boxalert]').on('submit', function (event) {
        if ($(this).data('boxalert-confirmado')) {
            return true;
        }

        event.preventDefault();
        abrirBoxalert($(this).data('boxalert'), this);
        return false;
    });

    $('[data-boxalert-cancelar], [data-boxalert-backdrop]').on('click', function (event) {
        if (event.target !== this) {
            return;
        }

        fecharBoxalert();
    });

    $('[data-boxalert-confirmar]').on('click', function () {
        if (! formularioBoxalert) {
            fecharBoxalert();
            return;
        }

        $(formularioBoxalert).data('boxalert-confirmado', true);
        formularioBoxalert.submit();
    });

    $('[data-instalar-pwa]').on('click', async function () {
        if (! instaladorPwa) {
            $(this).text('Instale pelo navegador');
            return;
        }

        instaladorPwa.prompt();
        await instaladorPwa.userChoice;
        instaladorPwa = null;
    });

    const carregarHorarios = function () {
        const $container = $('#horarios');
        const url = $container.data('url');

        if (! url) {
            $container.html('');
            return;
        }

        $container.html('<div class="empty-state">Carregando horarios...</div>');

        $.getJSON(url, { data: $('#data-agendamento').val() })
            .done(function (resposta) {
                if (! resposta.horarios.length) {
                    $container.html('<div class="empty-state">Nenhum horario disponivel para esta data.</div>');
                    return;
                }

                const grupos = {};

                resposta.horarios.forEach(function (horario) {
                    const chave = horario.profissional_id;
                    grupos[chave] = grupos[chave] || {
                        profissional: horario.profissional,
                        horarios: []
                    };
                    grupos[chave].horarios.push(horario);
                });

                $container.html(Object.values(grupos).map(function (grupo) {
                    const botoes = grupo.horarios.map(function (horario) {
                        const data = new Date(horario.inicio.replace(' ', 'T'));
                        const texto = data.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                        return `<button class="btn slot-button" type="button" data-slot-time="${texto}" data-slot-professional="${horario.profissional}" data-slot-professional-id="${horario.profissional_id}" data-slot-start="${horario.inicio}" data-slot-end="${horario.fim}">${texto}</button>`;
                    }).join('');

                    return `
                        <div class="slot-professional-row">
                            <strong>${grupo.profissional}</strong>
                            <div>${botoes}</div>
                        </div>
                    `;
                }).join(''));
            })
            .fail(function () {
                $container.html('<div class="empty-state">Nao foi possivel carregar os horarios.</div>');
            });
    };

    const gerarDiasDoMes = function () {
        const $faixa = $('[data-date-strip]');

        if (! $faixa.length) {
            return;
        }

        const hoje = new Date();
        const ultimoDia = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);
        const nomesDias = ['DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SAB'];
        const nomesMeses = ['JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ'];
        const valorInicial = $('#data-agendamento').val();
        const cards = [];
        const formatarDataLocal = function (data) {
            const ano = data.getFullYear();
            const mes = (data.getMonth() + 1).toString().padStart(2, '0');
            const dia = data.getDate().toString().padStart(2, '0');

            return `${ano}-${mes}-${dia}`;
        };
        const hojeValor = formatarDataLocal(hoje);

        for (let dia = new Date(hoje); dia <= ultimoDia; dia.setDate(dia.getDate() + 1)) {
            const data = new Date(dia);
            const valor = formatarDataLocal(data);
            const hojeTexto = valor === hojeValor ? 'HOJE' : data.getDate().toString().padStart(2, '0');
            const ativo = valor === valorInicial ? ' active' : '';

            cards.push(`
                <button class="chat-date-card${ativo}" type="button" data-date-value="${valor}">
                    <span>${nomesDias[data.getDay()]}</span>
                    <strong>${hojeTexto}</strong>
                    <small>${nomesMeses[data.getMonth()]}</small>
                </button>
            `);
        }

        $faixa.html(cards.join(''));
    };

    gerarDiasDoMes();

    $('[data-date-strip]').on('click', '[data-date-value]', function () {
        const dataValor = $(this).data('date-value');
        const textoDia = $(this).find('strong').text();
        const textoMes = $(this).find('small').text();

        esconderEnvioChat();
        $('#data-agendamento').val(dataValor);
        $('[data-date-value]').removeClass('active');
        $(this).addClass('active');
        $('[data-date-choice]').text(`${textoDia} ${textoMes}`).removeAttr('hidden');
        $('[data-time-choice], [data-final-chat-hint]').attr('hidden', true);
        carregarHorarios();
        rolarChatParaFinal();
    });

    $('#data-agendamento').on('change', carregarHorarios);
    carregarHorarios();

    const rolarChatParaFinal = function () {
        const $thread = $('.chat-thread');
        if ($thread.length) {
            $thread.stop().animate({ scrollTop: $thread[0].scrollHeight }, 420);
        }
    };

    let acaoChatPendente = null;
    let urlConfirmacaoAgendamento = null;

    const prepararEnvioChat = function (acao, textoBotao) {
        acaoChatPendente = acao;
        $('[data-chat-send-text]').text(textoBotao);
        $('[data-chat-send-panel]').removeAttr('hidden');
        rolarChatParaFinal();
    };

    const esconderEnvioChat = function () {
        acaoChatPendente = null;
        $('[data-chat-send-panel]').attr('hidden', true);
        $('[data-chat-send-text]').text('');
    };

    const voltarParaServicos = function () {
        esconderEnvioChat();
        $('[data-chat-finished]').attr('hidden', true);
        $('.chat-thread').removeAttr('hidden');
        $('[data-service-flow]').attr('hidden', true);
        $('[data-service-strip]').removeAttr('hidden');
        $('[data-chat-service-link]').removeClass('active');
        $('[data-service-choice-name], [data-date-choice], [data-time-choice]').text('');
        $('[data-date-choice], [data-time-choice], [data-final-chat-hint]').attr('hidden', true);
        $('[data-date-value]').removeClass('active');
        $('#horarios').data('url', '').html('');
        urlConfirmacaoAgendamento = null;
        rolarChatParaFinal();
    };

    if ($('body').is('[data-public-chat-service]')) {
        const navegacao = performance.getEntriesByType ? performance.getEntriesByType('navigation')[0] : null;
        const foiReload = navegacao ? navegacao.type === 'reload' : performance.navigation?.type === 1;
        const urlBase = $('body').data('public-chat-base');

        if (foiReload && urlBase) {
            window.location.replace(urlBase);
            return;
        }

        rolarChatParaFinal();
    }

    $('[data-chat-service-link]').on('click', function (event) {
        const $link = $(this);
        const nome = $link.data('service-name');
        const url = $link.data('service-url');
        const confirmUrl = $link.data('confirm-url');

        if (! nome || ! url || ! confirmUrl) {
            return true;
        }

        event.preventDefault();
        $('[data-chat-service-link]').removeClass('active');
        $link.addClass('active');
        prepararEnvioChat({
            tipo: 'servico',
            nome,
            url,
            confirmUrl
        }, `Enviar: ${nome}`);

        return false;
    });

    $('[data-chat-back-service]').on('click', voltarParaServicos);

    $('#horarios').on('click', '[data-slot-time]', function () {
        const horario = $(this).data('slot-time');
        const profissional = $(this).data('slot-professional');
        const profissionalId = $(this).data('slot-professional-id');
        const inicio = $(this).data('slot-start');
        const fim = $(this).data('slot-end');
        const texto = profissional ? `${horario} - ${profissional}` : horario;

        $('#horarios [data-slot-time]').removeClass('active');
        $(this).addClass('active');
        prepararEnvioChat({
            tipo: 'horario',
            texto,
            profissionalId,
            inicio,
            fim
        }, `Enviar: ${horario}`);
    });

    const adicionarAgendamentoNaLista = function (agendamento) {
        const $lista = $('[data-client-appointments]');
        const $menu = $('[data-toggle-client-appointments]');
        const $contador = $('[data-client-appointments-count]');

        if (! $lista.length || ! agendamento) {
            return;
        }

        const card = `
            <div class="chat-appointment-card">
                <strong>${agendamento.servico}</strong>
                <span>${agendamento.horario} com ${agendamento.profissional}</span>
                <small>Protocolo: ${agendamento.protocolo}</small>
            </div>
        `;

        $menu.removeAttr('hidden');
        $contador.text((Number($contador.text()) || 0) + 1);
        $lista.prepend(card);
        $('[data-final-appointments]').html($lista.html());
    };

    const mostrarTelaFinalAgendamento = function (resposta) {
        $('.chat-thread').attr('hidden', true);
        $('[data-chat-finished-message]').text(resposta.mensagem || 'Seu agendamento foi salvo com sucesso.');
        $('[data-final-appointments]').html($('[data-client-appointments]').html()).attr('hidden', true);
        $('[data-chat-finished]').removeAttr('hidden');
    };

    const salvarAgendamento = function (acao) {
        if (! urlConfirmacaoAgendamento) {
            return;
        }

        $('[data-chat-send-button]').prop('disabled', true).text('Salvando...');

        $.ajax({
            url: urlConfirmacaoAgendamento,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                profissional_id: acao.profissionalId,
                inicio: acao.inicio,
                fim: acao.fim
            }
        }).done(function (resposta) {
            $('[data-time-choice]').text(acao.texto).removeAttr('hidden');
            adicionarAgendamentoNaLista(resposta.agendamento);
            $('#horarios [data-slot-time].active').prop('disabled', true);
            mostrarTelaFinalAgendamento(resposta);
        }).fail(function (erro) {
            const mensagem = erro.responseJSON?.mensagem || 'Nao foi possivel salvar este agendamento. Escolha outro horario.';
            $('[data-final-chat-hint]').text(mensagem).removeAttr('hidden');
        }).always(function () {
            $('[data-chat-send-button]').prop('disabled', false).text('Enviar');
            esconderEnvioChat();
            rolarChatParaFinal();
        });
    };

    $('[data-toggle-client-appointments]').on('click', function () {
        const $lista = $('[data-client-appointments]');

        if (! $lista.length) {
            return;
        }

        $lista.prop('hidden', ! $lista.prop('hidden'));
        rolarChatParaFinal();
    });

    $('[data-final-show-appointments]').on('click', function () {
        $('[data-final-appointments]').prop('hidden', ! $('[data-final-appointments]').prop('hidden'));
    });

    $('[data-final-new-appointment]').on('click', voltarParaServicos);

    $('[data-chat-send-button]').on('click', function () {
        if (! acaoChatPendente) {
            return;
        }

        if (acaoChatPendente.tipo === 'servico') {
            $('[data-service-strip]').attr('hidden', true);
            $('[data-service-choice-name]').text(acaoChatPendente.nome);
            $('[data-service-flow]').removeAttr('hidden');
            $('[data-date-choice], [data-time-choice], [data-final-chat-hint]').attr('hidden', true);
            $('#horarios').data('url', acaoChatPendente.url);
            urlConfirmacaoAgendamento = acaoChatPendente.confirmUrl;
            gerarDiasDoMes();
            $('#horarios').html('<div class="empty-state">Selecione um dia para ver os horarios.</div>');
        }

        if (acaoChatPendente.tipo === 'horario') {
            salvarAgendamento(acaoChatPendente);
            return;
        }

        esconderEnvioChat();
        rolarChatParaFinal();
    });

    $('[data-plano-card]').on('click', function () {
        const planoId = $(this).data('plano-card').toString();
        $('[data-plano-select]').val(planoId).trigger('change');
        $('[data-plano-card]').removeClass('active');
        $(this).addClass('active');
    });

    const planoInicial = $('[data-plano-select]').val();
    if (planoInicial) {
        $(`[data-plano-card="${planoInicial}"]`).addClass('active');
    }

    if ($.fn.DataTable && $('.tabela-dados').length) {
        $('.tabela-dados').DataTable({
            language: {
                emptyTable: 'Nenhum registro encontrado',
                info: 'Mostrando _START_ ate _END_ de _TOTAL_ registros',
                infoEmpty: 'Mostrando 0 registros',
                infoFiltered: '(filtrado de _MAX_ registros)',
                lengthMenu: 'Mostrar _MENU_ registros',
                loadingRecords: 'Carregando...',
                processing: 'Processando...',
                search: 'Buscar',
                zeroRecords: 'Nenhum registro encontrado',
                paginate: {
                    first: '<<',
                    last: '>>',
                    next: '>',
                    previous: '<'
                }
            },
            pageLength: 10,
            responsive: true
        });
    }
});
