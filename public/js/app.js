(() => {
    const form = document.querySelector('.rating-form');
    if (!form) {
        return;
    }

    const hint = form.querySelector('[data-rating-hint]');
    const comment = form.querySelector('textarea[name="comment"]');
    const counter = form.querySelector('[data-comment-counter]');
    const labels = form.querySelectorAll('.star-rating label');
    const messages = {
        1: 'Vamos trabalhar para melhorar.',
        2: 'Obrigado, isso ajuda a corrigir a rota.',
        3: 'Valeu pelo retorno.',
        4: 'Que bom que o atendimento foi positivo.',
        5: 'Excelente, obrigado pela avaliação.'
    };

    labels.forEach((label) => {
        label.addEventListener('click', () => {
            const input = document.getElementById(label.getAttribute('for'));
            if (input && hint) {
                hint.textContent = messages[input.value] || 'Nota selecionada.';
            }
        });
    });

    const updateCounter = () => {
        if (!comment || !counter) {
            return;
        }

        const max = Number(comment.getAttribute('maxlength') || 0);
        counter.textContent = `${comment.value.length}/${max}`;
    };

    updateCounter();
    comment?.addEventListener('input', updateCounter);

    form.addEventListener('submit', (event) => {
        if (!form.querySelector('input[name="rating"]:checked')) {
            event.preventDefault();
            if (hint) {
                hint.textContent = 'Escolha uma nota antes de enviar.';
            }
        }
    });
})();

