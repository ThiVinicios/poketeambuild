// Análise de equipe com a tabela moderna de efetividade dos 18 tipos.
// A tipagem dos Pokémon continua vindo do banco; o cálculo considera todas
// as tipagens defensivas e mantém imunidades mesmo em combinações de tipos.
const NOMES_TIPOS = {
  normal:'Normal', fire:'Fogo', water:'Água', electric:'Elétrico',
  grass:'Planta', ice:'Gelo', fighting:'Lutador', poison:'Venenoso',
  ground:'Terrestre', flying:'Voador', psychic:'Psíquico',
  bug:'Inseto', rock:'Pedra', ghost:'Fantasma', dragon:'Dragão',
  dark:'Sombrio', steel:'Aço', fairy:'Fada'
}

const ORDEM_TIPOS = [
  'normal','fire','water','electric','grass','ice','fighting','poison',
  'ground','flying','psychic','bug','rock','ghost','dragon'
]

// Cada linha representa um tipo de golpe e informa contra quais tipos
// defensores ele é super eficaz, pouco eficaz ou não causa dano.
const TABELA = {
  normal:   { dobro:[], metade:['rock','steel'], zero:['ghost'] },
  fire:     { dobro:['grass','ice','bug','steel'], metade:['fire','water','rock','dragon'], zero:[] },
  water:    { dobro:['fire','ground','rock'], metade:['water','grass','dragon'], zero:[] },
  electric: { dobro:['water','flying'], metade:['electric','grass','dragon'], zero:['ground'] },
  grass:    { dobro:['water','ground','rock'], metade:['fire','grass','poison','flying','bug','dragon','steel'], zero:[] },
  ice:      { dobro:['grass','ground','flying','dragon'], metade:['fire','water','ice','steel'], zero:[] },
  fighting: { dobro:['normal','ice','rock','dark','steel'], metade:['poison','flying','psychic','bug','fairy'], zero:['ghost'] },
  poison:   { dobro:['grass','fairy'], metade:['poison','ground','rock','ghost'], zero:['steel'] },
  ground:   { dobro:['fire','electric','poison','rock','steel'], metade:['grass','bug'], zero:['flying'] },
  flying:   { dobro:['grass','fighting','bug'], metade:['electric','rock','steel'], zero:[] },
  psychic:  { dobro:['fighting','poison'], metade:['psychic','steel'], zero:['dark'] },
  bug:      { dobro:['grass','psychic','dark'], metade:['fire','fighting','poison','flying','ghost','steel','fairy'], zero:[] },
  rock:     { dobro:['fire','ice','flying','bug'], metade:['fighting','ground','steel'], zero:[] },
  ghost:    { dobro:['psychic','ghost'], metade:['dark'], zero:['normal'] },
  dragon:   { dobro:['dragon'], metade:['steel'], zero:['fairy'] },
  dark:     { dobro:['psychic','ghost'], metade:['fighting','dark','fairy'], zero:[] },
  steel:    { dobro:['ice','rock','fairy'], metade:['fire','water','electric','steel'], zero:[] },
  fairy:    { dobro:['fighting','dragon','dark'], metade:['fire','poison','steel'], zero:[] }
}

function multiplicador(ataque, tiposDefensor) {
  const regra = TABELA[ataque]
  if (!regra) return 1
  return tiposDefensor.reduce((total, defensor) => {
    // Imunidade prevalece sobre qualquer resistência ou fraqueza do outro tipo.
    if (regra.zero.includes(defensor)) return 0
    if (regra.dobro.includes(defensor)) return total * 2
    if (regra.metade.includes(defensor)) return total * 0.5
    return total
  }, 1)
}

function escaparHtml(valor) {
  return String(valor).replace(/[&<>"']/g, caractere => ({
    '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'
  })[caractere])
}

function formatarMultiplicador(valor) {
  if (valor === 0) return '0x'
  if (valor === 0.25) return '0.25x'
  if (valor === 0.5) return '0.5x'
  if (valor === 1) return '1x'
  if (valor === 2) return '2x'
  if (valor === 4) return '4x'
  return `${valor}x`
}

function nomeTipo(tipo) {
  return NOMES_TIPOS[tipo] || tipo
}

function classeMultiplicador(valor) {
  if (valor === 0) return 'mult-0'
  if (valor === 0.25) return 'mult-025'
  if (valor === 0.5) return 'mult-05'
  if (valor === 2) return 'mult-2'
  if (valor >= 4) return 'mult-4'
  return 'mult-1'
}

function pontuacaoDefensiva(valor) {
  // Resume a contribuição de cada Pokémon: fraqueza positiva,
  // resistência negativa e imunidade como proteção máxima.
  if (valor === 0) return -3
  return Math.log2(valor)
}

function desenharAnalise(elemento, membros) {
  if (!membros.length) {
    elemento.innerHTML = '<h2>Análise da equipe</h2><p class="analise-vazia">Escolha seus Pokémon para ver a análise de tipagens.</p>'
    return
  }

  const defesa = ORDEM_TIPOS.map(tipo => {
    const resultados = membros.map(pokemon => ({
      pokemon,
      valor: multiplicador(tipo, pokemon.tipos)
    }))
    const pontos = resultados.reduce((total, item) => total + pontuacaoDefensiva(item.valor), 0)
    const tooltip = resultados.map(({pokemon, valor}) =>
      `${pokemon.nome}: ${formatarMultiplicador(valor)}${valor > 1 ? ' (fraco)' : valor < 1 ? (valor === 0 ? ' (imune)' : ' (resistente)') : ' (neutro)'}`
    ).join('\n')
    return { tipo, pontos, tooltip }
  })

  const cobertura = ORDEM_TIPOS.map(tipo => {
    const resultados = membros.map(pokemon => {
      const valores = pokemon.tipos.map(tipoAtaque => multiplicador(tipoAtaque, [tipo]))
      return { pokemon, valor: Math.max(...valores) }
    })
    const eficazes = resultados.filter(item => item.valor > 1)
    const pontos = eficazes.length
    const tooltip = resultados.map(({pokemon, valor}) =>
      `${pokemon.nome}: ${formatarMultiplicador(valor)}${valor > 1 ? ' (vantagem)' : valor === 1 ? ' (neutro)' : ' (pouco eficaz)'} `
    ).join('\n')
    return { tipo, pontos, tooltip }
  })

  const renderGrade = (linhas, defensiva) => `
    <div class="analise-grade-tipos">
      ${linhas.map(({tipo, pontos, tooltip}) => `
        <div class="analise-tipo-item" tabindex="0" data-tipo="${tipo}" data-modo="${defensiva ? 'defesa' : 'ataque'}" aria-label="${escaparHtml(nomeTipo(tipo) + ': ' + tooltip.replace(/\n/g, ', '))}">
          <span class="tipo tipo-${tipo}">${escaparHtml(nomeTipo(tipo))}</span>
          <strong class="analise-pontuacao ${pontos > 0 ? 'pontuacao-positiva' : pontos < 0 ? 'pontuacao-negativa' : 'pontuacao-neutra'}">${pontos > 0 ? '+' : ''}${Number.isInteger(pontos) ? pontos : pontos.toFixed(1)}</strong>
        </div>
      `).join('')}
    </div>`

  elemento.innerHTML = `
    <h2>Análise da equipe</h2>
    <section class="analise-bloco">
      <div class="analise-titulo-bloco"><span class="analise-icone">🛡</span> Defesa da equipe</div>
      ${renderGrade(defesa, true)}
    </section>
    <section class="analise-bloco analise-bloco-ofensiva">
      <div class="analise-titulo-bloco"><span class="analise-icone">✥</span> Cobertura de tipos da equipe</div>
      ${renderGrade(cobertura, false)}
    </section>`

  // Mini-card próprio: só é acionado ao passar sobre o nome da tipagem.
  let card = document.querySelector('.analise-tooltip-flutuante')
  if (!card) {
    card = document.createElement('div')
    card.className = 'analise-tooltip-flutuante'
    card.setAttribute('role', 'tooltip')
    document.body.appendChild(card)
  }

  let esconderTimer
  let trocarTimer
  let alvoAtivo = null

  const preencherCard = (alvo) => {
    const tipo = alvo.dataset.tipo
    const modo = alvo.dataset.modo
    const lista = modo === 'defesa'
      ? membros.map(pokemon => ({ pokemon, valor: multiplicador(tipo, pokemon.tipos) }))
      : membros.map(pokemon => ({ pokemon, valor: Math.max(...pokemon.tipos.map(t => multiplicador(t, [tipo]))) }))

    const titulo = modo === 'defesa'
      ? `<strong class="tipo-texto-${tipo}">${escaparHtml(nomeTipo(tipo))}</strong> <span>causa...</span>`
      : `<span>Supereficaz contra </span><strong class="tipo-texto-${tipo}">${escaparHtml(nomeTipo(tipo))}</strong><span>:</span>`

    card.innerHTML = `<div class="analise-tooltip-titulo">${titulo}</div>` +
      lista.map(({pokemon, valor}) => {
        const detalhe = modo === 'defesa'
          ? (valor > 1 ? 'fraco' : valor === 0 ? 'imune' : valor < 1 ? 'resistente' : 'neutro')
          : (valor > 1 ? 'vantagem' : valor < 1 ? 'pouco eficaz' : 'neutro')
        const imagem = pokemon.imagem ? `<img src="${escaparHtml(pokemon.imagem)}" alt="" loading="lazy">` : ''
        return `<div class="analise-tooltip-linha">${imagem}<span>${escaparHtml(pokemon.nome)}</span><strong class="${classeMultiplicador(valor)}">${formatarMultiplicador(valor)}</strong><small>${detalhe}</small></div>`
      }).join('')

    const r = alvo.getBoundingClientRect()
    const largura = Math.min(260, window.innerWidth - 24)
    const esquerda = Math.min(Math.max(12, r.left + r.width / 2 - largura / 2), window.innerWidth - largura - 12)
    card.style.width = `${largura}px`
    card.style.left = `${esquerda}px`
    card.style.top = '0px'
    const altura = card.offsetHeight
    let topo = r.bottom + 7
    if (topo + altura > window.innerHeight - 8 && r.top - altura - 7 > 8) topo = r.top - altura - 7
    card.style.top = `${Math.max(8, topo)}px`
  }

  const mostrarCard = (alvo) => {
    clearTimeout(esconderTimer)
    clearTimeout(trocarTimer)
    if (alvoAtivo === alvo && card.classList.contains('visivel')) return

    const trocarConteudo = () => {
      preencherCard(alvo)
      alvoAtivo = alvo
      requestAnimationFrame(() => card.classList.add('visivel'))
    }

    if (card.classList.contains('visivel')) {
      card.classList.remove('visivel')
      trocarTimer = setTimeout(trocarConteudo, 90)
    } else {
      trocarConteudo()
    }
  }

  const ocultarCard = () => {
    clearTimeout(trocarTimer)
    clearTimeout(esconderTimer)
    esconderTimer = setTimeout(() => {
      card.classList.remove('visivel')
      alvoAtivo = null
    }, 35)
  }

  elemento.querySelectorAll('.analise-tipo-item').forEach(item => {
    const nome = item.querySelector('.tipo')
    // O mouse só ativa o card quando está exatamente sobre o nome do tipo,
    // nunca sobre a pontuação (+, - ou 0).
    nome.addEventListener('mouseenter', () => mostrarCard(item))
    nome.addEventListener('mouseleave', ocultarCard)
    item.addEventListener('focus', () => mostrarCard(item))
    item.addEventListener('blur', ocultarCard)
  })
}
