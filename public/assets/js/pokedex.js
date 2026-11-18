// Pokédex: filtra os cards pelo nome digitado e pelo tipo escolhido.
// Os cards já vêm prontos do PHP; aqui só escondemos e mostramos.

const campoBusca = document.getElementById('busca')
const chips = document.querySelectorAll('.chip')
const itens = document.querySelectorAll('.item-pokemon')
const contador = document.getElementById('contador')
const semResultado = document.getElementById('sem-resultado')

let tipoAtual = 'todos'

function filtrar() {
  const texto = campoBusca.value.trim().toLowerCase()
  let visiveis = 0

  itens.forEach((item) => {
    const combinaNome = item.dataset.nome.includes(texto)
    const combinaTipo = tipoAtual === 'todos' || item.dataset.tipos.split(' ').includes(tipoAtual)
    const mostrar = combinaNome && combinaTipo
    item.hidden = !mostrar
    if (mostrar) visiveis++
  })

  contador.textContent = visiveis
  semResultado.hidden = visiveis > 0
}

campoBusca.addEventListener('input', filtrar)

chips.forEach((chip) => {
  chip.addEventListener('click', () => {
    tipoAtual = chip.dataset.tipo
    chips.forEach((c) => c.classList.toggle('ativo', c === chip))
    filtrar()
  })
})
