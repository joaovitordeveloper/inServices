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
        if (!formularioBoxalert) {
            fecharBoxalert();
            return;
        }

        $(formularioBoxalert).data('boxalert-confirmado', true);
        formularioBoxalert.submit();
    });

    $('[data-instalar-pwa]').on('click', async function () {
        if (!instaladorPwa) {
            $(this).text('PWA disponivel no navegador');
            return;
        }

        instaladorPwa.prompt();
        await instaladorPwa.userChoice;
        instaladorPwa = null;
    });

    const carregarHorarios = function () {
        const $container = $('#horarios');
        const url = $container.data('url');

        if (!url) {
            return;
        }

        $container.html('<div class="empty-state">Carregando horarios...</div>');

        $.getJSON(url, { data: $('#data-agendamento').val() })
            .done(function (resposta) {
                if (!resposta.horarios.length) {
                    $container.html('<div class="empty-state">Nenhum horario disponivel para esta data.</div>');
                    return;
                }

                $container.html(resposta.horarios.map(function (horario) {
                    const data = new Date(horario.inicio.replace(' ', 'T'));
                    const texto = data.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                    return `<button class="btn btn-outline-success slot-button" type="button">${texto}<br><small>${horario.profissional}</small></button>`;
                }).join(''));
            })
            .fail(function () {
                $container.html('<div class="empty-state">Nao foi possivel carregar os horarios.</div>');
            });
    };

    $('#data-agendamento').on('change', carregarHorarios);
    carregarHorarios();

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
                    first: '«',
                    last: '»',
                    next: '›',
                    previous: '‹'
                }
            },
            pageLength: 10,
            responsive: true
        });
    }
});
