document.addEventListener('DOMContentLoaded', function () {

    const selects = document.querySelectorAll('.form-select');

    selects.forEach(select => {

        if (select.nextElementSibling && select.nextElementSibling.classList.contains('custom-select-wrapper')) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.classList.add('custom-select-wrapper');

        const trigger = document.createElement('div');
        trigger.classList.add('custom-select-trigger');
        const selectedOption = select.options[select.selectedIndex];

        const arrowSvg = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="custom-arrow-icon"><path d="m6 9 6 6 6-6"/></svg>`;

        let displayText = 'Select';
        if (selectedOption && selectedOption.value !== "") {
            displayText = selectedOption.text;
        } else if (selectedOption && selectedOption.value === "") {
            displayText = selectedOption.text || 'Select';
        }

        trigger.innerHTML = `<span>${displayText}</span>${arrowSvg}`;

        const optionsList = document.createElement('div');
        optionsList.classList.add('custom-options');

        Array.from(select.options).forEach(option => {

            const customOption = document.createElement('div');
            customOption.classList.add('custom-option');
            customOption.dataset.value = option.value;
            customOption.textContent = option.text;

            if (option.selected) {
                customOption.classList.add('selected');
            }

            if (option.value === "") {
                customOption.classList.add('option-all');
            }

            customOption.addEventListener('click', function (e) {
                e.stopPropagation();

                trigger.querySelector('span').textContent = this.textContent;

                select.value = this.dataset.value;

                optionsList.querySelectorAll('.custom-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');

                wrapper.classList.remove('open');

                const event = new Event('change');
                select.dispatchEvent(event);
            });

            optionsList.appendChild(customOption);
        });

        select.parentNode.insertBefore(wrapper, select.nextSibling);
        select.style.display = 'none';

        wrapper.appendChild(trigger);
        wrapper.appendChild(optionsList);

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();

            document.querySelectorAll('.custom-select-wrapper.open').forEach(opened => {
                if (opened !== wrapper) opened.classList.remove('open');
            });
            wrapper.classList.toggle('open');
        });
    });

    document.addEventListener('click', function (e) {
        document.querySelectorAll('.custom-select-wrapper.open').forEach(wrapper => {
            if (!wrapper.contains(e.target)) {
                wrapper.classList.remove('open');
            }
        });
    });
});
