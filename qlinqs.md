# Qlinqs — Contexto do Produto

Este documento explica **o que** o Qlinqs é e **por quê**, sem entrar em detalhe
técnico (isso vive no documento de estrutura de dados e nos `CLAUDE.md` de cada
parte do código). É a referência de intenção — o "norte" por trás das decisões.
Seu par técnico é o `qlinqs-estrutura-de-dados.md`.

## O que é

**Qlinqs** é um SaaS de "link na bio" — uma única página pública
(`qlinqs.com/seu-nome`) que reúne links, redes sociais, canais de contato e
conteúdo num só lugar, feita pra ir na bio das redes (Instagram, TikTok, etc.),
que só permitem um link clicável.

## O problema que existe hoje

O mercado já tem players estabelecidos — o **Linktree** é o mais conhecido, o
**Liinks** é um concorrente mais novo e mais rico em recursos. Olhando de perto,
os dois deixam a mesma lacuna aberta, por caminhos opostos:

- **Linktree** é fácil de usar, mas visualmente rígido: layout de coluna única,
  pouco controle real de design, sempre preso ao domínio deles.
- **Liinks** tem muito mais recurso (blocos variados, gates de conteúdo, gerador
  de background com efeitos), mas isso vem com complexidade — e, apesar da
  variedade, usuários relatam que **personalizar é confuso**. Não por falta de
  controle, e sim por *ergonomia*: você monta a partir de peças abstratas e tem
  que garimpar qual combinação de opções produz o que você já tinha na cabeça.

Ou seja: existe um vão entre "simples porém sem graça" e "rico porém complicado"
que nenhum dos dois preenche.

## A aposta do produto

**Personalização visual é a tese central, não um recurso a mais.** A página não
pode parecer um template genérico — o usuário precisa deixá-la com a cara da
própria marca/identidade **sem saber design nem código**. Toda decisão de escopo
do MVP gira em torno de proteger essa aposta: infraestrutura avançada e
monetização são cortadas primeiro; a camada de personalização, nunca.

A forma concreta de entregar isso é dupla:

1. **Templates prontos e bonitos** como porta de entrada — a pessoa escolhe uma
   "cara" completa num clique e a página inteira fica coerente, sem tocar em
   nenhum controle individual. Quem não quer pensar, não pensa.
2. **Blocos que partem de presets reconhecíveis** em vez de peças abstratas — a
   pessoa escolhe "Card com imagem", "Botão", "WhatsApp" já vendo a forma, e só
   ajusta o que quiser. Reconhecimento no lugar de garimpo.

Os controles finos continuam existindo por baixo, pra quem quiser mexer — mas
ninguém *precisa* começar por eles. Essa é a correção direta do que torna o Liinks
confuso.

Uma segunda aposta, logo atrás: a página tem que **funcionar como ferramenta de
apresentação**, não só empilhar links. Ela precisa carregar os blocos específicos
de que uma pessoa ou negócio depende pra parecer confiável e ser encontrável (um
CTA de contato, uma localização, um redirecionamento pro canal certo), estilizados
pra combinar com a identidade.

## Pra quem é

Pessoas e pequenos negócios que constroem sua presença em torno do Instagram e
precisam de um ponto de entrada único e apresentável, que também funcione como uma
porta de frente profissional leve — criadores de conteúdo, influenciadores,
negócios locais (clínicas, estúdios, lojas), freelancers e artistas. O primeiro
MVP é escopado e posicionado pro mercado brasileiro (PT-BR), onde o Instagram é
canal primário pra exatamente esse público.

São pessoas que vão de fato *olhar* pra própria página com frequência e se importar
com como ela aparece e como as representa — não só com "ter os links num lugar só".

Dois perfis ilustrativos que o produto precisa servir bem desde o dia um:

- **Negócio local (ex.: uma clínica):** quer um CTA de WhatsApp como ação
  principal, sua localização via mapa, e um redirecionamento pro Instagram ou
  outro perfil — uma página de frente profissional e confiável, mais que uma
  lista de links.
- **Criador/influenciador:** quer um link do Spotify, links de afiliado e um bloco
  que redireciona pro canal do YouTube — um hub pessoal que ainda parece a marca
  dele.

Os dois casos são cobertos pelos mesmos blocos (links, redirecionamentos, CTAs),
estilizados de forma diferente — é exatamente isso que "biosite personalizável"
significa na prática.

## Como o MVP foi escopado

A filosofia geral: **entregar algo pequeno, mas com a espinha certa** — features
cortadas ficam de fora por inteiro em vez de meio-implementadas, e a estrutura de
dados já nasce desenhada pra crescer sem precisar ser refeita depois.

**O que entra no v1:**

- Criar conta e reivindicar um link (`qlinqs.com/slug`).
- Montar a página com um conjunto de blocos práticos que cobrem tanto o caso do
  criador quanto o do negócio local: links (em layouts botão, thumbnail e card em
  destaque), redirecionamentos, CTA de WhatsApp/contato, bloco de
  localização/mapa, divisórias, texto e ícones sociais.
- Personalização profunda da aparência, entregue via **templates** + controles
  finos: paleta de cores, tipografia, forma dos elementos, efeito dos blocos e
  fundo (sólido ou gradiente simples) — a área em que o MVP mais investe, por ser
  o diferencial central.
- Analytics básico: views de página e cliques por bloco, o suficiente pra mostrar
  ao usuário que a página funciona, sem uma suíte completa.
- Ver a página pública renderizada, rápida e responsiva no mobile.

**O que fica de fora por enquanto — e por quê:**

- **Pagamento/assinatura:** não faz sentido cobrar antes de haver algo que valha a
  pena pagar.
- **Multi-perfil:** recurso de usuário avançado/agência — a maioria dos primeiros
  usuários só precisa de um perfil.
- **Domínio customizado:** um refinamento que só importa quando alguém já está
  usando o produto ativamente.
- **Gates de conteúdo/paywall:** recurso de monetização de criador, estágio mais
  avançado que o MVP.
- **Analytics avançado** (referrers, perfil de audiência, tendências temporais): o
  MVP prova que a página é usada; analytics profundo vem depois disso validado.
- **Recursos de "catálogo de possibilidades":** gerador de background animado
  (mesh/blobs/smoke), ícones 3D, geração de imagem por IA, galeria de imagens,
  carrossel e grid, preview automático de link (Open Graph). Ficam pra fases
  posteriores — a estrutura de dados já os comporta sem retrabalho.

Nenhum desses é um esquecimento — são cortes deliberados pra manter o foco na tese
central (personalização, mais amplitude funcional e feedback só o suficiente pra
ser genuinamente útil) e entregar mais rápido.

## Como isso se conecta às fases de desenvolvimento

O roadmap técnico segue esta ordem de prioridade: fundação primeiro (conta, perfil,
blocos básicos), depois personalização em profundidade, depois uma página pública
bem-feita com analytics básico — sempre nessa ordem, porque cortar a fase de
personalização pra "ganhar tempo" seria cortar a razão de o produto existir.

## Inspiração de features (referência, não pra copiar tal e qual)

O mapeamento detalhado dos blocos e opções de design do Liinks (feito como pesquisa
de mercado) serve como **catálogo de possibilidades** pra fases futuras — não como
lista de tarefas do MVP. Coisas como o gerador de background baseado em shader, os
ícones 3D, a geração de imagem por IA e o countdown que revela um bloco escondido
são exemplos do que pode ser construído mais tarde, uma vez que a fundação
(personalização simples e bem-estruturada, mais os blocos essenciais e analytics
básico) esteja sólida.
