// Código que roda em todas as páginas: janela de "Sair" e funções de apoio.

// Monta o endereço de um arquivo do site (respeita a base_url do config).
function urlBase(caminho) {
  const base = document.body.dataset.base || ''
  return base + '/' + caminho.replace(/^\//, '')
}

// Envia dados em JSON para uma rota da API e devolve a resposta já convertida.
async function enviarJSON(caminho, dados) {
  const token = document.querySelector('meta[name="csrf-token"]')?.content || ''
  const resposta = await fetch(urlBase(caminho), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': token },
    body: JSON.stringify(dados),
  })
  try {
    return await resposta.json()
  } catch {
    return { ok: false, mensagem: 'Resposta inesperada do servidor.' }
  }
}

// Confirmação de exclusão via modal
function confirmarExclusao(nome) {
  return new Promise((resolve) => {
    const modal = document.getElementById('modal-excluir')
    if (!modal) { resolve(false); return }
    const msgEl = modal.querySelector('.modal-excluir-msg')
    const btnSim = modal.querySelector('[data-confirmar-excluir]')
    const btnNao = modal.querySelector('[data-cancelar-excluir]')

    msgEl.textContent = 'Deseja excluir a equipe "' + nome + '"? Essa ação não pode ser desfeita.'
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

// Pergunta antes de excluir uma equipe e, se confirmado, apaga e recarrega a página.
async function excluirEquipe(id, nome) {
  const confirmado = await confirmarExclusao(nome)
  if (!confirmado) return
  const resultado = await enviarJSON('api/equipes.php', { acao: 'excluir', id: Number(id) })
  if (resultado.ok) {
    location.reload()
  } else {
    const mensagem = document.getElementById('mensagem')
    if (mensagem) {
      mensagem.textContent = resultado.mensagem
      mensagem.className = 'aviso aviso-erro'
      mensagem.hidden = false
    } else {
      console.error(resultado.mensagem)
    }
  }
}

// Botões "Excluir" das equipes (existem no montador e na página de equipes).
document.addEventListener('click', (evento) => {
  const botao = evento.target.closest('[data-excluir]')
  if (botao) {
    evento.stopPropagation()
    excluirEquipe(botao.dataset.excluir, botao.dataset.nome)
  }
})

// ----- Janela de confirmação para sair da conta -----
const modalSair = document.getElementById('modal-sair')
if (modalSair) {
  const abrir = document.getElementById('abrir-sair')
  const cancelar = document.getElementById('cancelar-sair')

  abrir.addEventListener('click', () => (modalSair.hidden = false))
  cancelar.addEventListener('click', () => (modalSair.hidden = true))
  // Clicar fora da caixinha ou apertar Esc também fecha.
  modalSair.addEventListener('click', (e) => {
    if (e.target === modalSair) modalSair.hidden = true
  })
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') modalSair.hidden = true
  })
}
