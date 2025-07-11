const startBtn = document.getElementById("start-btn");
const stopBtn = document.getElementById("stop-btn");
const textOutput = document.getElementById("text-output");

let recognition;

if ('webkitSpeechRecognition' in window) {
    recognition = new webkitSpeechRecognition();
    recognition.continuous = true;
    recognition.interimResults = true;
    recognition.lang = 'en-US'; // Set language (English-US)

    recognition.onstart = () => {
        console.log("Voice recognition started.");
    };

    recognition.onresult = (event) => {
        let transcript = '';
        for (let i = event.resultIndex; i < event.results.length; i++) {
            transcript += event.results[i][0].transcript;
        }
        textOutput.value = transcript;
    };

    recognition.onerror = (event) => {
        console.error("Error occurred: ", event.error);
    };

    recognition.onend = () => {
        console.log("Voice recognition stopped.");
        startBtn.disabled = false;
        stopBtn.disabled = true;
    };
} else {
    alert("Speech Recognition is not supported in your browser. Please try Chrome or Edge.");
}

startBtn.onclick = () => {
    startBtn.disabled = true;
    stopBtn.disabled = false;
    recognition.start();
};

stopBtn.onclick = () => {
    recognition.stop();
};