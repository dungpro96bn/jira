import { initEditor } from './editor.js';
import { initAdf, convertToAdf, prettyJson } from './adf-converter.js';

const outputElement = document.querySelector('#description');

async function init() {
    await initAdf();

    initEditor('.input-rich', (html) => {
        const adf = convertToAdf(html);
        outputElement.value = JSON.stringify(adf);
    });
}

init();