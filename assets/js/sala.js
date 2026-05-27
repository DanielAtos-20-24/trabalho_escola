function mostrarFoto() {
    const foto = document.getElementById('detalheFoto');
    const placeholder = document.getElementById('detalheFotoPlaceholder');

    foto.style.display = 'block';
    placeholder.classList.remove('active');
}

function mostrarPlaceholderFoto() {
    const foto = document.getElementById('detalheFoto');
    const placeholder = document.getElementById('detalheFotoPlaceholder');

    foto.removeAttribute('src');
    foto.style.display = 'none';
    placeholder.classList.add('active');
}

function mostrarDetalhes(botao) {
    document.querySelectorAll('.equipment-card').forEach(function(card) {
        card.classList.remove('selected');
    });

    botao.classList.add('selected');

    const painel = document.getElementById('detalhesEquipamento');
    const foto = document.getElementById('detalheFoto');
    const iconePlaceholder = document.getElementById('detalheIconePlaceholder');
    const caminhoFoto = botao.getAttribute('data-foto');

    painel.classList.add('active');
    iconePlaceholder.className = 'bi ' + botao.getAttribute('data-icone');

    foto.onload = mostrarFoto;
    foto.onerror = mostrarPlaceholderFoto;
    foto.alt = 'Foto do ' + botao.getAttribute('data-nome');

    if (caminhoFoto) {
        foto.src = caminhoFoto;
    } else {
        mostrarPlaceholderFoto();
    }

    document.getElementById('detalheNome').textContent = botao.getAttribute('data-nome');
    document.getElementById('detalheDescricao').textContent = botao.getAttribute('data-descricao');
    document.getElementById('detalheProcessador').textContent = botao.getAttribute('data-processador');
    document.getElementById('detalheMemoria').textContent = botao.getAttribute('data-memoria');
    document.getElementById('detalheArmazenamento').textContent = botao.getAttribute('data-armazenamento');
    document.getElementById('detalhePlacaVideo').textContent = botao.getAttribute('data-placa-video');
    document.getElementById('detalhePatrimonio').textContent = botao.getAttribute('data-patrimonio');
    document.getElementById('detalheSituacao').textContent = botao.getAttribute('data-situacao');

    painel.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
    });
}

document.querySelectorAll('.equipment-card').forEach(function(card) {
    card.addEventListener('click', function() {
        mostrarDetalhes(card);
    });
});