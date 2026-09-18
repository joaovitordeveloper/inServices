---
name: documentacao-codigo-fluxo
description: Use esta skill quando o usuário pedir para documentar um código, gerar documentação de fluxo de classes/sistema em markdown, ou ajustar/criar o README da aplicação com dados de subida de ambiente e fluxograma. Dispara com pedidos como "documenta esse código", "cria a documentação do fluxo", "ajusta o README com o setup de ambiente", ou quando o usuário aponta uma pasta/projeto e pede para "varrer" e explicar como funciona.
---

# Documentação de Código e Fluxo do Sistema

Gera dois artefatos a partir do código fornecido (arquivos anexados, pasta indicada, ou repositório acessível): **documentação de fluxo por classe** e **README ajustado com setup de ambiente e fluxograma**.

## Parte 1 — Documentação de fluxo (markdown)

Para cada classe relevante encontrada no código analisado:

- **Responsabilidade da classe**: o que ela faz, em uma frase objetiva.
- **Principais métodos**: nome, parâmetros, o que cada um faz e, quando relevante, o que retorna.
- **Dependências**: outras classes/serviços que ela usa ou que a usam (acoplamento).
- **Fluxo de execução**: quando a classe entra em ação dentro do sistema (chamada por qual rota, evento, comando, etc.), e o que ela dispara em seguida.

Depois de documentar as classes individualmente, feche com uma seção de **fluxo geral do sistema**: como as classes se conectam do ponto de entrada (controller, comando, endpoint) até o resultado final, narrando a ordem real de execução, não apenas listando classes soltas.

**Regras de escrita:**
- Clareza acima de tudo, frases curtas, sem jargão desnecessário, sem repetir a mesma informação em seções diferentes.
- Use nomes exatos de classes/métodos/variáveis como aparecem no código, nunca traduza ou renomeie.
- Não documente getters/setters triviais linha por linha, agrupe-os numa menção única se não agregam entendimento do fluxo.
- Se o código tiver lógica de negócio não óbvia (condições, regras específicas do domínio), explique o *porquê*, não só o *o quê*.
- Formato de saída: arquivo `.md` bem estruturado com headers hierárquicos (##, ###), não um bloco de texto corrido.

## Parte 2 — Ajuste do README

Atualize (ou crie, se não existir) o `README.md` do projeto incluindo:

- **Setup de ambiente**: dependências, versão de linguagem/runtime, variáveis de ambiente necessárias, comandos de instalação, como subir o projeto localmente (Docker Compose, servidor embutido, etc.), como rodar migrations/seeds se houver.
- **Fluxograma do funcionamento do sistema**: um diagrama Mermaid (` ```mermaid `) mostrando o fluxo principal identificado na Parte 1, do ponto de entrada até o resultado, com as decisões/ramificações relevantes.

**Regra crítica sobre dados faltantes**: se qualquer informação necessária para o setup de ambiente não estiver disponível ou clara no código analisado (ex: variável de ambiente sem valor de exemplo, versão de serviço externo não especificada, comando de subida ambíguo), **pergunte ao usuário antes de preencher essa parte do README**. Nunca invente valores de configuração, portas, versões ou comandos que não foram confirmados pelo código ou pelo usuário. Liste tudo que falta de uma vez, em vez de perguntar item por item.

## Ordem de execução

1. Analise o código fornecido e monte mentalmente o mapa de classes e fluxo.
2. Gere a documentação de fluxo (Parte 1) como arquivo markdown separado.
3. Monte o rascunho do README (Parte 2), incluindo o fluxograma Mermaid.
4. Antes de entregar o README, revise se há lacunas de setup de ambiente, se houver, pare e pergunte ao usuário especificamente o que falta.
5. Só entregue os arquivos finais depois que as lacunas forem resolvidas (ou se o usuário confirmar que não há mais nada a esclarecer).

## O que não fazer

- Não documente arquivos de configuração de terceiros (vendor, node_modules, etc.).
- Não gere documentação genérica de padrão de projeto (ex: "isso segue o padrão MVC") sem amarrar com o código real analisado.
- Não presuma stack, versão ou comando de setup que não esteja explícito no código ou confirmado pelo usuário.
