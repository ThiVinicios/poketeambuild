// Validação dos formulários de login e cadastro, antes de enviar ao servidor.
// (O PHP valida de novo, isto só avisa a pessoa mais rápido.)

const formulario = document.getElementById('form-login') || document.getElementById('form-cadastro')
const caixaErro = document.getElementById('erro-js')

formulario.addEventListener('submit', (evento) => {
  const dados = new FormData(formulario)
  const email = String(dados.get('email') || '').trim()
  const senha = String(dados.get('senha') || '')
  const nome = dados.has('nome') ? String(dados.get('nome')).trim() : null

  let erro = ''
  if (nome !== null && nome.length < 2) {
    erro = 'O nome precisa ter pelo menos 2 letras.'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    erro = 'Informe um e-mail válido.'
  } else if (senha.length < 8) {
    erro = 'A senha precisa ter pelo menos 8 caracteres.'
  }

  if (erro) {
    evento.preventDefault()
    caixaErro.textContent = erro
    caixaErro.hidden = false
  }
})
