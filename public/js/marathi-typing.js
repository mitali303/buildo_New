(function () {
    'use strict';

    var storageKey = 'buildo-marathi-mode';
    var marathiMode = localStorage.getItem(storageKey) !== 'off';
    var timers = new WeakMap();
    var composing = new WeakSet();
    var fieldStates = new WeakMap();

    function isSupportedField(element) {
        if (!(element instanceof HTMLInputElement || element instanceof HTMLTextAreaElement)) {
            return false;
        }

        if (element.disabled || element.readOnly || element.dataset.marathi === 'off') {
            return false;
        }

        if (element instanceof HTMLTextAreaElement) {
            return true;
        }

        return ['text', 'search'].includes((element.type || 'text').toLowerCase());
    }

    async function transliterate(text) {
        if (!text.trim()) {
            return text;
        }

        var url = 'https://inputtools.google.com/request'
            + '?text=' + encodeURIComponent(text)
            + '&itc=mr-t-i0-und&num=1&cp=0&cs=1&ie=utf-8&oe=utf-8';

        try {
            var response = await fetch(url);
            var data = await response.json();

            if (data[0] === 'SUCCESS' && data[1] && data[1][0] && data[1][0][1]) {
                return data[1][0][1][0];
            }
        } catch (error) {
            console.warn('Marathi transliteration unavailable.', error);
        }

        return text;
    }

    // function updateButton() {
    //     var button = document.getElementById('marathiModeButton');

    //     if (!button) {
    //         return;
    //     }

    //     button.textContent = marathiMode ? 'Marathi: ON' : 'English: ON';
    //     button.title = marathiMode
    //         ? 'English typing will be converted to Marathi'
    //         : 'English typing is enabled';
    //     button.setAttribute('aria-pressed', String(marathiMode));
    // }
    function updateButton() {
        var button = document.getElementById('marathiModeButton');
        var indicator = document.getElementById('languageIndicator');

        if (!button) { return; }

        button.style.color = marathiMode ? '#198754' : '#0d6efd';

        button.title = marathiMode ? 'Marathi' : 'English';

        button.setAttribute('aria-pressed', String(marathiMode));

        if (indicator) {
            indicator.textContent = marathiMode ? 'म' : 'Eng';
            indicator.style.color = marathiMode ? '#198754' : '#0d6efd';
        }
    }


    function convertField(element) {
        if (!isSupportedField(element) || composing.has(element)) {
            return;
        }

        var state = fieldStates.get(element);

        if (!marathiMode) {
            clearTimeout(timers.get(element));
            fieldStates.set(element, { baseValue: element.value, pendingValue: '' });
            return;
        }

        if (!state || !element.value.startsWith(state.baseValue) || element.selectionStart !== element.value.length) {
            fieldStates.set(element, { baseValue: element.value, pendingValue: '' });
            return;
        }

        state.pendingValue = element.value.slice(state.baseValue.length);

        clearTimeout(timers.get(element));
        timers.set(element, setTimeout(async function () {
            var pendingBeforeRequest = state.pendingValue;
            var translated = await transliterate(pendingBeforeRequest);

            if (element.value === state.baseValue + pendingBeforeRequest) {
                element.value = state.baseValue + translated;
                element.setSelectionRange(state.baseValue.length + translated.length, state.baseValue.length + translated.length);
                state.baseValue = element.value;
                state.pendingValue = '';
                element.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }, 500));
    }

    document.addEventListener('focusin', function (event) {
        if (isSupportedField(event.target)) {
            fieldStates.set(event.target, { baseValue: event.target.value, pendingValue: '' });
        }
    });

    document.addEventListener('compositionstart', function (event) {
        if (isSupportedField(event.target)) {
            composing.add(event.target);
        }
    });

    document.addEventListener('compositionend', function (event) {
        if (isSupportedField(event.target)) {
            composing.delete(event.target);
            convertField(event.target);
        }
    });

    document.addEventListener('input', function (event) {
        convertField(event.target);
    });

    document.addEventListener('click', function (event) {
    if (event.target.closest('#marathiModeButton')) {

        marathiMode = !marathiMode;

        localStorage.setItem(
            storageKey,
            marathiMode ? 'on' : 'off'
        );

        updateButton();

        alert(marathiMode ? 'Marathi' : 'English');
    }
});
}());