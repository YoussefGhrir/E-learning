// Ouvrir/fermer le modal
const chatbotToggle = document.getElementById('chatbot-toggle');
const chatbotModal = document.getElementById('chatbot-modal');
const chatbotClose = document.getElementById('chatbot-close');

chatbotToggle.addEventListener('click', () => {
    chatbotModal.style.display = 'block';
});

chatbotClose.addEventListener('click', () => {
    chatbotModal.style.display = 'none';
});

// Envoyer un message au chatbot
const chatbotForm = document.getElementById('chatbot-form');
const chatbotInput = document.getElementById('chatbot-input');
const chatbotMessages = document.getElementById('chatbot-messages');

chatbotForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const userMessage = chatbotInput.value.trim();

    if (!userMessage) return;

    // Ajouter le message de l'utilisateur
    chatbotMessages.innerHTML += `
        <div class="message">
            <strong>Vous</strong>
            <p>${userMessage}</p>
        </div>
    `;

    // Envoyer le message au backend
    try {
        const response = await fetch('/chatbot', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message: userMessage }),
        });

        if (!response.ok) {
            throw new Error('Erreur lors de la communication avec le chatbot');
        }

        const data = await response.json();

        // Ajouter la réponse du chatbot
        chatbotMessages.innerHTML += `
            <div class="message">
                <strong>Chatbot</strong>
                <p>${data.response}</p>
            </div>
        `;
    } catch (error) {
        console.error('Erreur:', error);
    }

    // Vider le champ de saisie
    chatbotInput.value = '';

    // Faire défiler vers le bas
    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
});