// Confirmações personalizadas somente nas ações explicitamente configuradas.
document.querySelectorAll('form[data-confirmar]').forEach((form) => {
  form.addEventListener('submit', (evento) => {
    if (form.dataset.confirmado === '1') return
    evento.preventDefault()
    const modal = document.getElementById('modal-admin-confirmacao')
    if (!modal) return
    modal.querySelector('.modal-admin-msg').textContent = form.dataset.confirmar
    const sim = modal.querySelector('[data-admin-confirmar]')
    const nao = modal.querySelector('[data-admin-cancelar]')
    modal.hidden = false
    const fechar = (ok) => {
      modal.hidden = true
      sim.removeEventListener('click', onSim)
      nao.removeEventListener('click', onNao)
      modal.removeEventListener('click', onFora)
      if (ok) {
        form.dataset.confirmado = '1'
        form.requestSubmit()
      }
    }
    const onSim = () => fechar(true)
    const onNao = () => fechar(false)
    const onFora = (e) => { if (e.target === modal) fechar(false) }
    sim.addEventListener('click', onSim)
    nao.addEventListener('click', onNao)
    modal.addEventListener('click', onFora)
  })
})
