// Montador de equipes: escolher Pokémon, ver a análise ao vivo e salvar.

const MAX = 6

const slots = document.getElementById('slots')
const contagem = document.getElementById('contagem')
const campoNome = document.getElementById('nome-equipe')
const mensagem = document.getElementById('mensagem')
const botaoSalvar = document.getElementById('salvar')
const botaoCancelar = document.getElementById('cancelar')
const campoBusca = document.getElementById('busca')
const analise = document.getElementById('analise')
const botoes = document.querySelectorAll('.escolha')

// Equipe que está sendo montada: lista de Pokémon escolhidos.
let escolhidos = []
let equipeId = null

// Transforma o botão do card num objeto simples.
function dadosDoBotao(botao) {
  return {
    id: Number(botao.dataset.id),
    nome: botao.dataset.nome,
    imagem: botao.dataset.imagem,
    tipos: botao.dataset.tipos.split(' '),
  }
}

function mostrarMensagem(texto, tipo) {
  mensagem.textContent = texto
  mensagem.className = 'aviso aviso-' + tipo
  mensagem.hidden = !texto
}

// Desenha os 6 espaços da equipe, o contador, a análise e marca os cards escolhidos.
function atualizar() {
  slots.innerHTML = ''
  for (let i = 0; i < MAX; i++) {
    const pokemon = escolhidos[i]
    const slot = document.createElement('button')
    slot.type = 'button'
    slot.className = 'slot'
    if (pokemon) {
      // Exibe os tipos como símbolos compactos, usando ícones locais do projeto.
      const tiposHtml = pokemon.tipos
        .map((tipo) => {
          const nomeTipo = typeof NOMES_TIPOS !== 'undefined' ? NOMES_TIPOS[tipo] || tipo : tipo
          return '<img class="slot-tipo-icone" src="' +
            urlBase('assets/img/tipos/' + tipo + '.svg') +
            '" alt="' + nomeTipo + '" title="' + nomeTipo + '">'
        })
        .join('')
      slot.innerHTML =
        '<span class="slot-foto"><img alt=""></span>' +
        '<span class="slot-info"><span class="slot-nome">' + pokemon.nome + '</span>' +
        '<span class="slot-tipos">' + tiposHtml + '</span></span>' +
        '<span class="slot-x">&times;</span>'
      const img = slot.querySelector('img')
      img.src = pokemon.imagem
      img.alt = pokemon.nome
      slot.title = 'Remover ' + pokemon.nome
      // A imagem e o restante do slot não removem o Pokémon: somente o X.
      slot.addEventListener('click', (evento) => {
        if (evento.target.closest('.slot-x')) alternar(pokemon)
      })
    } else {
      slot.innerHTML = '<span class="slot-mais">+</span>'
      slot.disabled = true
    }
    slots.appendChild(slot)
  }

  contagem.textContent = escolhidos.length + '/' + MAX + ' escolhidos'
  contagem.classList.toggle('cheio', escolhidos.length === MAX)

  botoes.forEach((botao) => {
    botao.classList.toggle(
      'ativo',
      escolhidos.some((p) => p.id === Number(botao.dataset.id)),
    )
  })

  desenharAnalise(analise, escolhidos)
}

// Escolhe ou remove um Pokémon. Aqui também barramos o 7º Pokémon.
function alternar(pokemon) {
  mostrarMensagem('', 'ok')
  const indice = escolhidos.findIndex((p) => p.id === pokemon.id)
  if (indice >= 0) {
    escolhidos.splice(indice, 1)
  } else if (escolhidos.length < MAX) {
    escolhidos.push(pokemon)
  } else {
    mostrarMensagem('Uma equipe pode ter no máximo 6 Pokémon.', 'erro')
  }
  atualizar()
}

botoes.forEach((botao) => botao.addEventListener('click', () => alternar(dadosDoBotao(botao))))

// Busca na lista de Pokémon do montador.
const avisoBuscaVazia = document.getElementById('busca-sem-resultados')
function filtrarPokemon() {
  const texto = campoBusca.value.trim().toLowerCase()
  let encontrados = 0
  botoes.forEach((botao) => {
    const mostrar = botao.dataset.nome.toLowerCase().includes(texto)
    botao.hidden = !mostrar
    if (mostrar) encontrados++
  })
  avisoBuscaVazia.hidden = encontrados !== 0
}
campoBusca.addEventListener('input', filtrarPokemon)

// Salvar: valida e manda para a API.
botaoSalvar.addEventListener('click', async () => {
  const nome = campoNome.value.trim()
  if (nome.length < 2) return mostrarMensagem('Dê um nome à equipe.', 'erro')
  if (escolhidos.length < 1) return mostrarMensagem('Escolha pelo menos 1 Pokémon.', 'erro')

  if (escolhidos.length < 6) {
    const confirmado = await confirmarIncompleta(escolhidos.length)
    if (!confirmado) return
  }

  botaoSalvar.disabled = true
  const resultado = await enviarJSON('api/equipes.php', {
    acao: 'salvar',
    id: equipeId,
    nome: nome,
    pokemon: escolhidos.map((p) => p.id),
  })
  botaoSalvar.disabled = false

  if (resultado.ok) {
    // Recarrega para a lista "Minhas equipes" da lateral já aparecer atualizada.
    sessionStorage.setItem('tb_msg', resultado.mensagem)
    location.href = urlBase('montar.php')
  } else {
    mostrarMensagem(resultado.mensagem, 'erro')
  }
})

botaoCancelar.addEventListener('click', () => {
  const destino = urlBase('montar.php')
  if (!escolhidos.length) {
    location.href = destino
    return
  }
  destinoPendente = destino
  if (modalNavegacao) modalNavegacao.hidden = false
})

// Confirmação de equipe incompleta via modal
function confirmarIncompleta(quantidade) {
  return new Promise((resolve) => {
    const modal = document.getElementById('modal-incompleta')
    const msgEl = modal.querySelector('.modal-incompleta-msg')
    const btnSim = modal.querySelector('[data-confirmar]')
    const btnNao = modal.querySelector('[data-cancelar]')

    msgEl.textContent = 'Sua equipe tem apenas ' + quantidade + ' de 6 Pokémon. Deseja salvar mesmo assim?'
    modal.hidden = false

    function fechar(resultado) {
      modal.hidden = true
      btnSim.removeEventListener('click', onSim)
      btnNao.removeEventListener('click', onNao)
      modal.removeEventListener('click', onFora)
      resolve(resultado)
    }

    const onSim = () => fechar(true)
    const onNao = () => fechar(false)
    const onFora = (e) => { if (e.target === modal) fechar(false) }

    btnSim.addEventListener('click', onSim)
    btnNao.addEventListener('click', onNao)
    modal.addEventListener('click', onFora)
  })
}

// ----- Início -----
// Se estamos editando uma equipe, carrega os dados dela.
const edicao = JSON.parse(document.getElementById('dados-edicao').textContent)
if (edicao) {
  equipeId = edicao.id
  campoNome.value = edicao.nome
  escolhidos = edicao.pokemon
    .map((id) => document.querySelector('.escolha[data-id="' + id + '"]'))
    .filter(Boolean)
    .map(dadosDoBotao)
  document.getElementById('salvar').textContent = 'Atualizar equipe'
  botaoCancelar.hidden = false
}

// Mensagem de "salvo com sucesso" vinda do recarregamento anterior.
const msgGuardada = sessionStorage.getItem('tb_msg')
if (msgGuardada) {
  mostrarMensagem(msgGuardada, 'ok')
  sessionStorage.removeItem('tb_msg')
}

atualizar()

// Impede perder uma equipe com Pokémon selecionados ao navegar por links internos.
// A saída da conta é excluída desta regra, pois já possui confirmação própria.
let destinoPendente = null
const modalNavegacao = document.getElementById('modal-navegacao')
const continuarEditando = modalNavegacao?.querySelector('[data-continuar-editando]')
const ignorarNavegacao = modalNavegacao?.querySelector('[data-ignorar-navegacao]')
document.addEventListener('click', (evento) => {
  const link = evento.target.closest('a[href]')
  if (!link || !escolhidos.length || !modalNavegacao) return
  if (link.id === 'abrir-sair' || link.closest('#modal-sair')) return
  const href = link.getAttribute('href')
  if (!href || href.startsWith('#') || link.target === '_blank' || evento.ctrlKey || evento.metaKey || evento.shiftKey || evento.altKey) return
  evento.preventDefault()
  destinoPendente = link.href
  modalNavegacao.hidden = false
})
continuarEditando?.addEventListener('click', () => {
  modalNavegacao.hidden = true
  destinoPendente = null
})
ignorarNavegacao?.addEventListener('click', () => {
  modalNavegacao.hidden = true
  if (destinoPendente) location.href = destinoPendente
})
modalNavegacao?.addEventListener('click', (evento) => {
  if (evento.target === modalNavegacao) {
    modalNavegacao.hidden = true
    destinoPendente = null
  }
})
