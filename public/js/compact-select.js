// Keep the native select as the submitted value and as the no-JavaScript fallback.
document.querySelectorAll('[data-compact-select]').forEach(select => {
    const id = select.id;
    const wrapper = select.parentElement;
    const button = document.createElement('button');
    const list = document.createElement('div');
    const choices = Array.from(select.options).filter(option => option.value);
    let active = 0;
    let search = '';
    let searchTimer;

    select.id = `${id}-native`;
    button.id = id;
    button.type = 'button';
    button.className = 'compact-select-button';
    button.setAttribute('role', 'combobox');
    button.setAttribute('aria-haspopup', 'listbox');
    button.setAttribute('aria-expanded', 'false');
    button.setAttribute('aria-required', String(select.required));
    const label = document.querySelector(`label[for="${id}"]`);
    if (label) {
        label.id = `${id}-label`;
        button.setAttribute('aria-labelledby', `${label.id} ${id}-value`);
    }
    const value = document.createElement('span');
    value.id = `${id}-value`;
    button.append(value);
    list.id = `${id}-choices`;
    list.className = 'compact-select-list';
    list.setAttribute('role', 'listbox');
    list.setAttribute('aria-label', label?.textContent.trim() || 'Choices');
    list.hidden = true;
    button.setAttribute('aria-controls', list.id);
    wrapper.append(button);
    // In-flow placement keeps the menu below the field without covering other inputs.
    wrapper.after(list);

    function sync() {
        value.textContent = select.selectedOptions[0]?.textContent || 'Select department';
        Array.from(list.children).forEach((item, index) => {
            item.setAttribute('aria-selected', String(choices[index].value === select.value));
        });
    }
    function highlight(index) {
        active = (index + choices.length) % choices.length;
        Array.from(list.children).forEach((item, i) => item.classList.toggle('is-active', i === active));
        button.setAttribute('aria-activedescendant', list.children[active].id);
        list.children[active].scrollIntoView({ block: 'nearest' });
    }
    function close() {
        list.hidden = true;
        button.setAttribute('aria-expanded', 'false');
        button.removeAttribute('aria-activedescendant');
    }
    function open() {
        list.hidden = false;
        button.setAttribute('aria-expanded', 'true');
        highlight(Math.max(0, choices.findIndex(option => option.value === select.value)));
    }
    function choose(index) {
        select.value = choices[index].value;
        select.dispatchEvent(new Event('input', { bubbles: true }));
        select.dispatchEvent(new Event('change', { bubbles: true }));
        button.removeAttribute('aria-invalid');
        close();
        button.focus({ preventScroll: true });
    }
    choices.forEach((option, index) => {
        const item = document.createElement('div');
        item.id = `${id}-choice-${index}`;
        item.className = 'compact-select-option';
        item.setAttribute('role', 'option');
        item.textContent = option.textContent;
        item.addEventListener('click', () => choose(index));
        list.append(item);
    });
    button.addEventListener('click', () => list.hidden ? open() : close());
    button.addEventListener('keydown', event => {
        if (['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
            event.preventDefault();
            const wasClosed = list.hidden;
            if (wasClosed) open();
            if (event.key === 'Home') highlight(0);
            else if (event.key === 'End') highlight(choices.length - 1);
            else if (!wasClosed) highlight(active + (event.key === 'ArrowDown' ? 1 : -1));
        } else if (!list.hidden && ['Enter', ' '].includes(event.key)) {
            event.preventDefault();
            choose(active);
        } else if (event.key === 'Escape') {
            event.preventDefault();
            close();
        } else if (event.key === 'Tab') close();
        else if (event.key.length === 1 && event.key !== ' ' && !event.ctrlKey && !event.metaKey && !event.altKey) {
            event.preventDefault();
            search += event.key.toLowerCase();
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => { search = ''; }, 700);
            if (list.hidden) open();
            const index = choices.findIndex(option => option.textContent.toLowerCase().startsWith(search));
            if (index >= 0) highlight(index);
        }
    });
    document.addEventListener('click', event => {
        if (!wrapper.contains(event.target) && !list.contains(event.target)) close();
    });
    select.addEventListener('change', sync);
    select.addEventListener('invalid', event => {
        event.preventDefault();
        button.setAttribute('aria-invalid', 'true');
        // Do not steal focus from an earlier invalid field.
        if (select.form?.querySelector(':invalid') === select) { button.focus(); open(); }
    });
    select.form?.addEventListener('reset', () => setTimeout(() => {
        sync(); close(); button.removeAttribute('aria-invalid');
    }, 0));
    sync();
    select.hidden = true;
});
