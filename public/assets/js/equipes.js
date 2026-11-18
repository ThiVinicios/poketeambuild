// Página "Minhas equipes": clicar numa equipe abre os detalhes e a análise dela.

const cartoes = document.querySelectorAll('.equipe-card')
const detalhes = document.querySelectorAll('.detalhe')

cartoes.forEach((cartao) => {
  cartao.addEventListener('click', () => {
    const painel = document.getElementById('detalhe-' + cartao.dataset.equipe)
    const jaAberto = !painel.hidden

    // Fecha todos e, se o clicado não estava aberto, abre ele.
    detalhes.forEach((d) => (d.hidden = true))
    cartoes.forEach((c) => c.classList.remove('ativo'))
    if (jaAberto) return

    painel.hidden = false
    cartao.classList.add('ativo')

    // A análise é calculada só na primeira vez que a equipe é aberta.
    const alvo = painel.querySelector('.analise')
    if (!alvo.dataset.pronto) {
      desenharAnalise(alvo, JSON.parse(alvo.dataset.membros))
      alvo.dataset.pronto = '1'
    }
    painel.scrollIntoView({ behavior: 'smooth', block: 'start' })
  })
})
