import init, { convert } from "https://unpkg.com/htmltoadf@0.1.10/htmltoadf.js";

export async function initAdf() {
    await init();
}

export function convertToAdf(html) {
    return JSON.parse(convert(html));
}

export function prettyJson(obj) {
    return JSON.stringify(obj, null, 3);
}