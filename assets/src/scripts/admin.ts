import Alpine from "alpinejs";

import testApiConnection from "./ajax/test-api-connection";
import synchronization from "./ajax/synchronization";

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('testApiConnection', testApiConnection);
    Alpine.data('synchronization', synchronization);
});

Alpine.start();