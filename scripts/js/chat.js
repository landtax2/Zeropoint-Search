function send_chat() {
    var toSend = new URLSearchParams({
        system_prompt: document.getElementById('system_prompt').value,
        user_prompt: document.getElementById('user_prompt').value,
        user_data: document.getElementById('user_data').value,
        action: 'chat'
    }).toString();

    // Calculate the context window size - not exact but a good guess
    const userPrompt = document.getElementById('user_prompt').value;
    const userData = document.getElementById('user_data').value;
    const contextWindow = (userPrompt.split(/\s+/).length + userData.split(/\s+/).length) * 2.5;

    // Add the context window size to the data being sent
    toSend += '&context_window=' + encodeURIComponent(contextWindow);

    Swal.fire({
        title: 'Please Wait',
        html: 'Chat is processing',
        allowOutsideClick: false,
    });
    Swal.showLoading();

    return new Promise((resolve, reject) => {
        fetch('/application_api/chat/index.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: toSend
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(data => {
                process_send_chat(data);
                resolve(data);
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'An error occurred while processing your request.', 'error');
                reject(error);
            });
    });
}

function process_send_chat(resp) {
    Swal.close();
    document.getElementById('chat_result').value = resp;
}

function copyTextFromChatResult() {
    var chatResultText = document.getElementById('chat_result');
    chatResultText.select();
    chatResultText.setSelectionRange(0, 99999); // For mobile devices

    navigator.clipboard.writeText(chatResultText.value)
        .then(() => {
            // Alert the user that the text has been copied
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Chat result has been copied to clipboard.',
                timer: 1500,
                showConfirmButton: false
            });
        })
        .catch(err => {
            console.error('Failed to copy text: ', err);
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Failed to copy text. Please try again.',
            });
        });
}

function rechat() {
    const userPrompt = document.getElementById('user_prompt');
    const chatResult = document.getElementById('chat_result');
    const userData = document.getElementById('user_data');
    const userPromptLabel = document.getElementById('user_prompt_label');
    const userDataLabel = document.getElementById('user_data_label');
    const rechatButton = document.getElementById('rechat_button');

    userPrompt.value = 'User: ' + userPrompt.value;
    // Move chat result to user prompt with AI tagging
    userPrompt.value += "\n\nAI: " + chatResult.value;

    // Clear chat result and user data
    chatResult.value = '';
    userData.value = '';

    // Rename labels
    userPromptLabel.textContent = 'Chat History';
    userDataLabel.textContent = 'New Chat';

    // Increase the number of rows in the chat history textarea
    userPrompt.rows = 10;

    // Remove the rechat button
    rechatButton.style.display = 'none';
}

function resetAIChatModal() {
    document.getElementById('system_prompt').value = '';
    document.getElementById('user_prompt').value = '';
    document.getElementById('user_data').value = '';
    document.getElementById('chat_result').value = '';
    document.getElementById('user_prompt_label').textContent = 'User Prompt';
    document.getElementById('user_data_label').textContent = 'User Data';
    document.getElementById('system_prompt').rows = 2;
    document.getElementById('user_prompt').rows = 2;
    document.getElementById('user_data').rows = 6;
    document.getElementById('chat_result').rows = 15;
}

function open_chat(prompt, user_template) {
    const elements = {
        'system_prompt': 'You are a helpful, smart, kind, and efficient AI assistant. You always fulfill the user\'s requests to the best of your ability.',
        'user_prompt': prompt,
        'user_data': user_template,
        'chat_result': ''
    };

    Object.entries(elements).forEach(([id, value]) => {
        document.getElementById(id).value = value;
    });
    // Focus on the user_data field for immediate input after a short delay
    setTimeout(function () {
        document.getElementById('user_data').focus();
    }, 500);

}